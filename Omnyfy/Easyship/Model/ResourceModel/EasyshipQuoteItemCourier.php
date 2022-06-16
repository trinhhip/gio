<?php
namespace Omnyfy\Easyship\Model\ResourceModel;

class EasyshipQuoteItemCourier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('omnyfy_easyship_quoteitem_courier', 'quoteitem_id');
    }
}
