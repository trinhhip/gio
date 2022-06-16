<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 2019-05-07
 * Time: 14:03
 */
namespace Omnyfy\RebateCore\Model\ResourceModel\Vendor\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends \Omnyfy\RebateCore\Model\ResourceModel\Vendor\Collection implements SearchResultInterface
{
    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria=null)
    {
        return $this;
    }

    public function getTotalCount()
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount)
    {
        return $this;
    }

    public function setItems(array $items=null)
    {
        return $this;
    }
}