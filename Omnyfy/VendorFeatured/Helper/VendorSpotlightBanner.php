<?php
namespace Omnyfy\VendorFeatured\Helper;

class VendorSpotlightBanner extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;
    protected $resourceConnection;
    protected $vendorFactory;
    protected $vendorMedia;
    protected $bannerCollectionFactory;
    protected $bannerVendorCollectionFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Omnyfy\Vendor\Model\VendorFactory $vendorFactory
     * @param \Omnyfy\Vendor\Helper\Media $vendorMedia
     * @param \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory $bannerCollectionFactory
     * @param \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerVendor\CollectionFactory $bannerVendorCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Omnyfy\Vendor\Helper\Media $vendorMedia,
        \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory $bannerCollectionFactory,
        \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerVendor\CollectionFactory $bannerVendorCollectionFactory
    ){
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->vendorFactory = $vendorFactory;
        $this->vendorMedia = $vendorMedia;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->bannerVendorCollectionFactory = $bannerVendorCollectionFactory;
    }

    public function getConfigValue($path){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $storeScope);
    }

    public function isSpotlightBannerEnabled(){
        return $this->getConfigValue('omnyfy_vendor_featured/spotlight_banner/is_enabled');
    }

    public function getBannerTitle(){
        return $this->getConfigValue('omnyfy_vendor_featured/spotlight_banner/title');
    }

    public function getMobileBreakpoint(){
        return $this->getConfigValue('omnyfy_vendor_featured/spotlight_banner/breakpoint');
    }

    /**
     * Get Banner Placement by category_id
     *
     * @return array|null
     */
    public function getBannerPlacementByCategoryId($categoryId){
        if ($categoryId) {
            $connection = $this->resourceConnection->getConnection();
            $bannerTable = $this->resourceConnection->getTableName('omnyfy_spotlight_banner_placement');
            $bannerVendorTable = $this->resourceConnection->getTableName('omnyfy_spotlight_banner_vendor');

            $sql = "SELECT banner.*, COUNT(bannervendor.banner_vendor_id) AS ads_spots
                FROM ".$bannerTable." banner
                LEFT JOIN ".$bannerVendorTable." bannervendor ON banner.banner_id = bannervendor.banner_id
                WHERE CONCAT(',',REPLACE(banner.category_ids, ' ', ''),',') LIKE '%,".$categoryId.",%'
                GROUP BY banner.banner_id
                HAVING ads_spots > 0";

            $banners = $connection->fetchAll($sql);
            return $banners;
        }else{
            return null;
        }
    }

    /**
     * Get Banner Placement by vendor_id
     *
     * @return array|null
     */
    public function getBannerPlacementByVendorId($vendorId){
        if ($vendorId) {
            $connection = $this->resourceConnection->getConnection();
            $bannerTable = $this->resourceConnection->getTableName('omnyfy_spotlight_banner_placement');
            $bannerVendorTable = $this->resourceConnection->getTableName('omnyfy_spotlight_banner_vendor');

            $sql = "SELECT banner.*, COUNT(bannervendor.banner_vendor_id) AS ads_spots
                FROM ".$bannerTable." banner
                LEFT JOIN ".$bannerVendorTable." bannervendor ON banner.banner_id = bannervendor.banner_id
                WHERE CONCAT(',',REPLACE(banner.vendor_ids, ' ', ''),',') LIKE '%,".$vendorId.",%'
                GROUP BY banner.banner_id
                HAVING ads_spots > 0";

            $banners = $connection->fetchAll($sql);
            return $banners;
        }else{
            return null;
        }
    }

    /**
     * Get assigned vendor on a banner
     */
    public function getAssignedVendorOnBanner($bannerId){
        if ($bannerId) {
            $collection = $this->bannerVendorCollectionFactory->create();
            $collection->addFieldToFilter('main_table.banner_id', $bannerId);
            return $collection;
        }else{
            return null;
        }
    }

    public function getVendorLogo($vendorId){
        $vendor = $this->vendorFactory->create()->load($vendorId);
        return $this->vendorMedia->getVendorLogoUrl($vendor);
    }
}