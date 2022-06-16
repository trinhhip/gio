<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel;

class SpotlightBannerPlacement extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_spotlight_banner_placement', 'banner_id');
    }
}