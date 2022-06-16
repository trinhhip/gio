<?php
namespace Omnyfy\VendorFeatured\Helper;

class PromoWidget extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $resourceConnection;
    protected $scopeConfig;
    protected $productModel;
    protected $collectionFactory;
    protected $vendorFactory;
    protected $vendorMedia;
    
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Omnyfy\VendorFeatured\Model\ResourceModel\PromoVendorWidget\CollectionFactory $collectionFactory,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Omnyfy\Vendor\Helper\Media $vendorMedia
    ){
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        $this->productModel = $productModel;
        $this->collectionFactory = $collectionFactory;
        $this->vendorFactory = $vendorFactory;
        $this->vendorMedia = $vendorMedia;
    }

    public function getWidgetContent(){
        $collection = $this->collectionFactory->create()->setOrder('sort_order', 'ASC')->load();
        $widgets = $collection->getData();
        $arrWidgets = [];

        if (count($widgets) > 0) {
            foreach ($widgets as $widget) {
                $logo = $this->getVendorLogo($widget['vendor_id']);
                $product = $this->getLatestProductsByVendor($widget['vendor_id']);
                
                $widget['logo'] = $logo;
                $widget['products'] = $product;

                array_push($arrWidgets, $widget);
            }
        }
        return $arrWidgets;
    }

    public function getLatestProductsByVendor($vendorId){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $productNum = $this->scopeConfig->getValue('omnyfy_vendor_featured/promo_widget/product_num', $storeScope);

        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('omnyfy_vendor_vendor_product');
        $query = "SELECT product_id FROM " . $table . 
            " WHERE vendor_id=". $vendorId .
            " ORDER BY product_id DESC"
            ;
        $result = $connection->fetchAll($query);
        $notVisible = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE;
        $arrProduct = [];

        if (count($result) > 0) {
            foreach ($result as $value) {
                if (count($arrProduct) < $productNum) {
                    $product = $this->productModel->create()->load($value['product_id']);
                    if ($product->getStatus() == 1 && $product->getVisibility() != $notVisible) {
                        array_push($arrProduct, $product);
                    }
                }
            }
        }
        return $arrProduct;
    }

    public function getVendorLogo($vendorId){
        $vendor = $this->vendorFactory->create()->load($vendorId);
        return $this->vendorMedia->getVendorLogoUrl($vendor);
    }
}