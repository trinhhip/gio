<?php
namespace Omnyfy\VendorFeatured\Model;

class SpotlightBannerVendor extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerVendor');
    }

    public function getVendorsOnBanner($banner_id){
        $collection = $this->getCollection()
            ->addFieldToFilter('omnyfy_spotlight_banner_placement.banner_id', $banner_id)
        ;
        if (count($collection) > 0) {
            return $collection;
        }else{
            return null;
        }
    }

    public function getBannersByVendorId($vendor_id){
        $collection = $this->getCollection()
            ->addFieldToFilter('main_table.vendor_id', $vendor_id)
        ;
        if (count($collection) > 0) {
            return $collection;
        }else{
            return null;
        }
    }

    public function getBannersByBannerId($banner_id){
        $collection = $this->getCollection()
            ->addFieldToFilter('main_table.banner_id', $banner_id)
        ;
        if (count($collection) > 0) {
            return $collection;
        }else{
            return null;
        }
    }
}