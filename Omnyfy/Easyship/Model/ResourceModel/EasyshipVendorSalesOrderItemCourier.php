<?php
namespace Omnyfy\Easyship\Model\ResourceModel;

class EasyshipVendorSalesOrderItemCourier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_isPkAutoIncrement = false;
    
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ){
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_easyship_vendor_salesorderitem_courier', 'item_id');
    }
}
