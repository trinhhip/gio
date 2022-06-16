<?php
namespace Omnyfy\Easyship\Model\ResourceModel;

class EasyshipSelectedCourier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
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
        $this->_init('omnyfy_easyship_selected_courier', 'entity_id');
    }
}
