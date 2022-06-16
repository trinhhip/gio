<?php
namespace Omnyfy\Mcm\Model\ResourceModel;


class VendorPayoutType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('omnyfy_mcm_vendor_payout_type', 'id');
    }
}
