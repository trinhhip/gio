<?php

namespace Omnyfy\Mcm\Model\ResourceModel\CategoryCommissionReport\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Omnyfy\Mcm\Model\ResourceModel\CategoryCommissionReport\Collection as CategoryCommissionReportCollection;

class Collection extends CategoryCommissionReportCollection implements SearchResultInterface {

    public function _construct() {
        $this->_init(
                'Magento\Framework\View\Element\UiComponent\DataProvider\Document', 'Omnyfy\Mcm\Model\ResourceModel\CategoryCommissionReport'
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAggregations() {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations) {
        $this->aggregations = $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllIds($limit = null, $offset = null) {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Clone and reset collection
     *
     * @param null $limit
     * @param null $offset
     * @return Select
     */
    protected function _getAllIdsSelect($limit = null, $offset = null) {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->_idFieldName);
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria() {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null) {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount() {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount($totalCount) {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null) {
        return $this;
    }

}
