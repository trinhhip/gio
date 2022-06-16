<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-21
 * Time: 14:48
 */
namespace Omnyfy\Approval\Model\Resource\Product\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends \Omnyfy\Approval\Model\Resource\Product\Collection implements SearchResultInterface
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
 