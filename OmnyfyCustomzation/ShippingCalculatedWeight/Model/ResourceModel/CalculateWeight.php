<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CalculateWeight extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('shipping_calculate_weight', 'entity_id');
    }
}
