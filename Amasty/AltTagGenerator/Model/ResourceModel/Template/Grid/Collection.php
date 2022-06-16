<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\ResourceModel\Template\Grid;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\Template as TemplateResource;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\Collection as TemplateCollection;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\Store\Table;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Zend_Db_Expr;

class Collection extends TemplateCollection implements SearchResultInterface
{
    const STORES_FIELD = 'stores';

    /**
     * @var AggregationInterface
     */
    private $aggregations;

    public function _construct()
    {
        $this->_init(Document::class, TemplateResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
            ['store_table' => $this->getTable(Table::NAME)],
            sprintf('main_table.%s = store_table.%s', TemplateInterface::ID, Table::TEMPLATE_COLUMN),
            [
                self::STORES_FIELD => new Zend_Db_Expr(
                    sprintf('GROUP_CONCAT(%s SEPARATOR \'%s\')', Table::STORE_COLUMN, ',')
                )
            ]
        );
        $this->getSelect()->group(TemplateInterface::ID);

        parent::_renderFiltersBefore();
    }
}
