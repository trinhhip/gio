<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'banner_id';

    protected function _construct()
    {
        $this->_init('Omnyfy\VendorFeatured\Model\SpotlightBannerPlacement', 'Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement');
    }
}