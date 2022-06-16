<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Model\ResourceModel\CalculateWeight;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'calculate_weight';
    protected $_eventObject = 'calculate_weight';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\OmnyfyCustomzation\ShippingCalculatedWeight\Model\CalculateWeight::class,
            \OmnyfyCustomzation\ShippingCalculatedWeight\Model\ResourceModel\CalculateWeight::class);
    }
}