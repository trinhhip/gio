<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.22
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SearchElastic\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface as TypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match as ElasticsearchMatch;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerPool;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Mirasvit\Search\Model\ConfigProvider;
use Mirasvit\Search\Service\QueryService;

class Match extends ElasticsearchMatch
{
    private $queryService;

    private $fieldMapper;

    private $attributeProvider;

    private $config;

    public function __construct(
        QueryService $queryService,
        FieldMapperInterface $fieldMapper,
        AttributeProvider $attributeProvider,
        TypeResolver $fieldTypeResolver,
        ValueTransformerPool $valueTransformerPool,
        Config $config
    ) {
        $this->queryService         = $queryService;
        $this->fieldMapper          = $fieldMapper;
        $this->attributeProvider    = $attributeProvider;
        $this->config               = $config;

        parent::__construct($fieldMapper, $attributeProvider, $fieldTypeResolver, $valueTransformerPool, $config);
    }

    /**
     * @param string $conditionType
     */
    public function build(array $selectQuery, RequestQueryInterface $requestQuery, $conditionType): array
    {
        $queryValue = $requestQuery->getValue();
        $searchQuery = $this->queryService->build($queryValue);
        $matches = $this->filterMatches($requestQuery->getMatches());
        $fields = [];

        foreach ($matches as $match) {
            $boost = (int)($match['boost'] ?? 1);

            $resolvedField = $this->fieldMapper->getFieldName(
                $match['field'],
                ['type' => FieldMapperInterface::TYPE_QUERY]
            );
            if ($resolvedField === '_search') {
                $resolvedField = '_misc';
            }

            $fields[$resolvedField] = $boost;
        }

        $queryBody = $this->compileQuery($searchQuery, $fields);

        if (!isset($selectQuery['bool']['should'])) {
            $selectQuery['bool']['should'] = $queryBody;
        }

        return $selectQuery;
    }

    private function compileQuery(array $query, array $fields): array
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $compiled = $this->compileQuery($value, $fields);
                    break;

                case '$!like':
                    $q        = $this->compileQuery($value, $fields);
                    $compiled = [
                        'bool' => [
                            'must_not' => $q['bool']['must'],
                        ],
                    ];
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($item, $fields);
                    }
                    if (count($and)) {
                        $compiled['bool']['must'] = $and;
                    }
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($item, $fields);
                    }

                    if (count($or)) {
                        $compiled['bool']['should'] = $or;
                    }

                    break;

                case '$term':
                    foreach ($fields as $field => $boost) {
                        $phrase = $this->escape($value['$phrase']);
                        if ($field == '_misc') {
                            $compiled['bool']['should'][]['match_phrase'] = [
                                $field => [
                                    'query' => $value['$phrase'],
                                    'boost' => $boost,
                                ],
                            ];
                        }
                        switch ($value['$wildcard']) {
                            case ConfigProvider::WILDCARD_INFIX:
                                $compiled['bool']['should'][]['wildcard'] = [
                                    $field => [
                                        'value' => "*$phrase*",
                                        'boost' => $boost,
                                    ],
                                ];
                                break;

                            case ConfigProvider::WILDCARD_PREFIX:
                                $compiled['bool']['should'][]['wildcard'] = [
                                    $field => [
                                        'value' => "*$phrase",
                                        'boost' => $boost,
                                    ],
                                ];
                                break;

                            case ConfigProvider::WILDCARD_SUFFIX:
                                $compiled['bool']['should'][]['wildcard'] = [
                                    $field => [
                                        'value' => "$phrase*",
                                        'boost' => $boost,
                                    ],
                                ];
                                break;

                            case ConfigProvider::WILDCARD_DISABLED:
                                $compiled['bool']['should'][]['match_phrase'] = [
                                    $field => [
                                        'query' => $value['$phrase'],
                                        'boost' => $boost,
                                    ],
                                ];
                                break;
                        }
                    }
                    break;
            }
        }

        return $compiled;
    }

    private function filterMatches(array $matches): array
    {
        foreach ($matches as $key => $field) {
            if (isset($field['boost']) && $field['field'] != '_misc') {
                if (in_array($this->attributeProvider->getByAttributeCode($field['field'])->getFrontendInput(), ['price', 'weight'])) {
                    unset($matches[$key]);
                }
            }
        }

        return $matches;
    }

    private function escape(string $value): string
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
