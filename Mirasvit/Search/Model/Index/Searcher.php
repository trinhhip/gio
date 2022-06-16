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



namespace Mirasvit\Search\Model\Index;

use Magento\CatalogSearch\Model\Advanced\Request\BuilderFactory as RequestBuilderFactory;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\SearchEngine;
use Mirasvit\Search\Api\Data\Index\InstanceInterface;

class Searcher
{
    private $instance;

    private $queryFactory;

    private $requestBuilderFactory;

    private $searchEngine;

    private $scopeResolver;

    private $responseFactory;

    public function __construct(
        QueryFactory $queryFactory,
        RequestBuilderFactory $requestBuilderFactory,
        SearchEngine $searchEngine,
        ScopeResolverInterface $scopeResolver,
        ResponseFactory $responseFactory
    ) {
        $this->queryFactory          = $queryFactory;
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->searchEngine          = $searchEngine;
        $this->scopeResolver         = $scopeResolver;
        $this->responseFactory       = $responseFactory;
    }

    public function getInstance(): InstanceInterface
    {
        return $this->instance;
    }

    public function setInstance(InstanceInterface $instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * Join matches to collection
     *
     * @param AbstractDb $collection
     * @param string     $field
     *
     * @return $this
     */
    public function joinMatches($collection, string $field = 'e.entity_id')
    {
        $queryResponse = $this->getQueryResponse();
        $ids = [0];

        if ($queryResponse) {
            foreach ($queryResponse->getIterator() as $item) {
                $ids[] = $item->getId();
            }
        }

        $idList    = join(',', $ids);
        $whereExpr = new \Zend_Db_Expr("$field IN ($idList)");
        $collection->getSelect()
            ->reset(\Magento\Framework\DB\Select::ORDER)
            ->where($whereExpr->__toString())
            ->order(new \Zend_Db_Expr("FIELD($field, $idList)"));

        return $this;
    }

    /**
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse()
    {
        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();

        if ($query->isQueryTextShort()) {
            return false;
        }

        $requestBuilder = $this->requestBuilderFactory->create();

        $queryText = $this->queryFactory->get()->getQueryText();

        $requestBuilder->bind('search_term', $queryText);

        $requestBuilder->bindDimension('scope', $this->scopeResolver->getScope());

        $requestBuilder->setRequestName($this->getInstance()->getIdentifier());

        $queryRequest = $requestBuilder->create();

        return $this->searchEngine->search($queryRequest);
    }

    /**
     * @return array
     */
    public function getMatchedIds()
    {
        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        if ($query->isQueryTextShort()) {
            return [];
        }

        $queryText = $query->getQueryText();

        $requestBuilder = $this->requestBuilderFactory->create();

        $requestBuilder->bind('search_term', $queryText);

        $requestBuilder->bindDimension('scope', $this->scopeResolver->getScope());

        $requestBuilder->setRequestName($this->getInstance()->getIdentifier());

        /** @var \Magento\Framework\Search\Request $queryRequest */
        $queryRequest = $requestBuilder->create();

        $queryResponse = $this->searchEngine->search($queryRequest);
        $ids           = [];
        foreach ($queryResponse->getIterator() as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }

    /**
     * @return array
     */
    private function getEmptyResult()
    {
        return [
            'documents'    => [],
            'aggregations' => [
                'price_bucket'    => [],
                'category_bucket' => [],
            ],
            'total'        => 0,
        ];
    }
}
