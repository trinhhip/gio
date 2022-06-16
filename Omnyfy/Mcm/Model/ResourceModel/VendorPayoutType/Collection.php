<?php
namespace Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'omnyfy_mcm_vendor_payout_type_collection';
    protected $_eventObject = 'vendor_payout_type_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\Mcm\Model\VendorPayoutType::class, \Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType::class);
    }
}
