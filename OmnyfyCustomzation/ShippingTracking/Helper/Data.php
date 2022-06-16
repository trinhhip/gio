<?php


namespace OmnyfyCustomzation\ShippingTracking\Helper;



use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\Context;
use Omnyfy\Vendor\Model\Resource\Vendor;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory;
use Omnyfy\Vendor\Model\VendorFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const THUMBNAIL_PRODUCT_IMAGE_ID = 'product_thumbnail_image';
    /**
     * @var Image
     */
    public $image;
    /**
     * @var ProductFactory
     */
    public $productFactory;
    /**
     * @var VendorFactory
     */
    public $vendorFactory;
    /**
     * @var CollectionFactory
     */
    private $vendorCollectionFactory;
    /**
     * @var Vendor
     */
    private $vendor;

    /**
     * Data constructor.
     * @param Context $context
     * @param Image $image
     * @param ProductFactory $productFactory
     * @param VendorFactory $vendorFactory
     * @param CollectionFactory $vendorCollectionFactory
     * @param Vendor $vendor
     */
    public function __construct(
        Context $context,
        Image $image,
        ProductFactory $productFactory,
        VendorFactory $vendorFactory,
        CollectionFactory $vendorCollectionFactory,
        Vendor $vendor
    )
    {
        parent::__construct($context);
        $this->image = $image;
        $this->productFactory = $productFactory;
        $this->vendorFactory = $vendorFactory;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->vendor = $vendor;
    }

    public function getThumbnailItem(\Magento\Catalog\Model\Product $product)
    {
        return $this->image->init($product, self::THUMBNAIL_PRODUCT_IMAGE_ID)
            ->resize(200, 200)
            ->getUrl();
    }

    public function getProductById($productId)
    {
        return $this->productFactory->create()->load($productId);
    }

    public function getVendor($vendorId){
        return $this->vendorFactory->create()->load($vendorId);
    }
    public function getVendorByProductId($productId){
        return $this->vendor->getVendorIdByProductId($productId);
    }
}