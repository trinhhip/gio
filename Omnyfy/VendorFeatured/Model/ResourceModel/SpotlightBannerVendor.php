<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel;

class SpotlightBannerVendor extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_spotlight_banner_vendor', 'banner_vendor_id');
    }
}