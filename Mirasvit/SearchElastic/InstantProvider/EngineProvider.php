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

namespace Mirasvit\SearchElastic\InstantProvider;

use Elasticsearch\Client;
use Mirasvit\SearchAutocomplete\InstantProvider\InstantProvider;

class EngineProvider extends InstantProvider
{
    private $query = [];

    public function getResults(string $indexIdentifier): array
    {
        $this->query = [
            'index' => $this->configProvider->getIndexName($indexIdentifier),
            'body'  => [
                'from'  => 0,
                'size'  => $this->configProvider->getLimit($indexIdentifier),
                'stored_fields' => [
                    '_id',
                    '_score',
                    '_source',
                ],
                'sort' => [
                    [
                        '_score' => [
                            'order' => 'desc',
                        ]
                    ]
                ],
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                    ],
                ],
            ],
        ];

        $this->setMustCondition($indexIdentifier);
        $this->setShouldCondition($indexIdentifier);

        try {
            $rawResponse = $this->getClient()->search($this->query);
        } catch (\Exception $e) {
            return [
                'totalItems' => 0,
                'items'      => [],
            ];
        }

        if ($this->configProvider->getEngine() == 'elasticsearch6') {
            $totalItems = (int)$rawResponse['hits']['total'];
        } else {
            $totalItems = (int)$rawResponse['hits']['total']['value'];
        }

        $items      = [];

        foreach ($rawResponse['hits']['hits'] as $data) {
            if (!isset($data['_source']['_instant'])) {
                continue;
            }

            if (!$data['_source']['_instant']) {
                continue;
            }

            $items[] = $data['_source']['_instant'];
        }

        return [
            'totalItems' => count($items) > 0 ? $totalItems : 0,
            'items'      => $items,
        ];
    }

    private function setMustCondition(string $indexIdentifier): void
    {
        if ($indexIdentifier === 'catalogsearch_fulltext') {
            $this->query['body']['query']['bool']['must'] = [
                'terms' => [
                    'visibility' => ['3', '4'],
                ],
            ];
        }
    }

    private function setShouldCondition(string $indexIdentifier): void
    {
        $shouldCondition = [];
        $fields = $this->configProvider->getIndexFields($indexIdentifier);
        $fields['_misc'] = 1;

        $requiredFields = [];

        if ($indexIdentifier === 'catalogsearch_fulltext') {
            $requiredFields ['_misc'] = $fields['_misc'];
            $requiredFields ['name'] = $fields['name'];
            $requiredFields ['sku'] = $fields['sku'];
        }

        $searchQuery = $this->queryService->build($this->getQueryText());
        $queryBody = [];

        foreach ($fields as $resolvedField => $boost) {
            $boost = (int)($match['boost'] ?? 1);

            if ($resolvedField === '_search') {
                $resolvedField = '_misc';
            }

            $q = $this->compileQuery($searchQuery, $resolvedField, $boost);

            if ($q) {
                $queryBody = array_merge_recursive($queryBody, $q);
            }
        }

        if (!isset($this->query['body']['query']['bool'])) {
            $this->query['body']['query']['bool'] = [];
        }

        $this->query['body']['query']['bool'] = array_merge($this->query['body']['query']['bool'], $queryBody);
    }


private function compileQuery(array $query, string $field, int $boost, bool $isNotLike = false): array
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $compiled['should'][] = $this->compileQuery($value, $field, $boost, false);
                    break;

                case '$!like':
                    $q = $this->compileQuery($value, $field, $boost, true);
                    $compiled['must_not'] = $q;
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($item, $field, $boost, $isNotLike);
                    }
                    if (count($and)) {
                        if ($isNotLike) {
                            $compiled = $and;
                        } else {
                            $compiled['bool']['must'] = $and;
                        }
                    }
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($item, $field, $boost, $isNotLike);
                    }

                    if (count($or)) {
                        if ($isNotLike) {
                            $compiled = $and;
                        } else {
                            $compiled['bool']['should'] = $or;
                        }
                    }

                    break;

                case '$term':
                    $phrase = $this->escape($value['$phrase']);
                    switch ($value['$wildcard']) {
                        case $this->configProvider::WILDCARD_INFIX:
                            $compiled['wildcard'] = [
                                $field => [
                                    'value' => "*$phrase*",
                                    'boost' => $boost,
                                ],
                            ];
                            break;

                        case $this->configProvider::WILDCARD_PREFIX:
                            $compiled['wildcard'] = [
                                $field => [
                                    'value' => "*$phrase",
                                    'boost' => $boost,
                                ],
                            ];
                            break;

                        case $this->configProvider::WILDCARD_SUFFIX:
                            $compiled['wildcard'] = [
                                $field => [
                                    'value' => "$phrase*",
                                    'boost' => $boost,
                                ],
                            ];
                            break;

                        case $this->configProvider::WILDCARD_DISABLED:
                            $compiled['match_phrase'] = [
                                $field => [
                                    'query' => $phrase,
                                    'boost' => $boost,
                                ],
                            ];
                            break;
                    }
                    break;
            }
        }

        return $compiled;
    }

    private function getClient(): Client
    {
        return \Elasticsearch\ClientBuilder::fromConfig($this->configProvider->getEngineConnection(), true);
    }
}
