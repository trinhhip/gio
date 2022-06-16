<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerVendor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'banner_vendor_id';

    protected function _construct()
    {
        $this->_init('Omnyfy\VendorFeatured\Model\SpotlightBannerVendor', 'Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerVendor');
    }

    protected function _initSelect() {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                'omnyfy_spotlight_banner_placement',
                'main_table.banner_id = omnyfy_spotlight_banner_placement.banner_id'
            )
            ->order('sort_order ASC');
    }
}