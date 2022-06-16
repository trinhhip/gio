<?php


namespace OmnyfyCustomzation\B2C\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BuyerAccount extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('b2c_customer_approval', 'entity_id');
    }
}
