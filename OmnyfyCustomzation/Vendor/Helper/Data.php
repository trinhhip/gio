<?php


namespace OmnyfyCustomzation\Vendor\Helper;


use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Omnyfy\Vendor\Model\Resource\Vendor;

class Data extends AbstractHelper
{
    const PRODUCT_IMAGE_ID = 'product_base_image';
    const XML_PATH_NUMBER_PRODUCT = 'omnyfy_vendor/vendor_listing_page/number_product_banner';
    const XML_PATH_BANNER_URL = 'omnyfy_vendor/vendor_listing_page/banner_image';
    /**
     * @var Vendor
     */
    public $vendor;
    /**
     * @var CollectionFactory
     */
    public $productCollectionFactory;
    /**
     * @var Image
     */
    public $imageHelper;

    public function __construct(
        Context $context,
        Vendor $vendor,
        CollectionFactory $productCollectionFactory,
        Image $imageHelper
    )
    {
        $this->vendor = $vendor;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        parent::__construct($context);
    }

    public function getProductByVendor($vendor)
    {
        $limit = $this->getNumberProductBanner();
        $productIds = $this->vendor->getProductIdsByVendorId($vendor->getId());
        $products = $this->productCollectionFactory->create();
        $products->addFieldToSelect('*');
        $products->addIdFilter($productIds);
        $products->setVisibility([Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG]);
        $products->setPageSize($limit);
        return $products;
    }

    public function getProductImage($product)
    {
        $image = $this->imageHelper->init($product, self::PRODUCT_IMAGE_ID)
            ->resize(125, 125);
        return $image->getUrl();
    }


    public function getNumberProductBanner()
    {
        $numberProduct = $this->scopeConfig->getValue(self::XML_PATH_NUMBER_PRODUCT, ScopeInterface::SCOPE_STORE);
        return $numberProduct ? $numberProduct : 8;
    }

    public function getBannerImage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BANNER_URL, ScopeInterface::SCOPE_STORE);
    }
}
