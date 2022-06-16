<?php


namespace OmnyfyCustomzation\Mcm\Helper;


use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Omnyfy\Vendor\Model\VendorFactory;

class Data extends AbstractHelper
{
    const THUMBNAIL_PRODUCT_IMAGE_ID = 'product_thumbnail_image';
    /**
     * @var Image
     */
    public $image;
    /**
     * @var VendorFactory
     */
    public $vendorFactory;
    /**
     * @var ProductRepository
     */
    public $productRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param Image $image
     * @param ProductRepository $productRepository
     * @param VendorFactory $vendorFactory
     */
    public function __construct(
        Context $context,
        Image $image,
        ProductRepository $productRepository,
        VendorFactory $vendorFactory
    )
    {
        parent::__construct($context);
        $this->image = $image;
        $this->productRepository = $productRepository;
        $this->vendorFactory = $vendorFactory;
    }

    public function getThumbnailItem($product)
    {
        if ($product instanceof Product) {
            $image = $this->image->init($product, self::THUMBNAIL_PRODUCT_IMAGE_ID)
                ->resize(200, 200)->getUrl();
        } else {
            $image = $this->image->getDefaultPlaceholderUrl('thumbnail');
        }
        return $image;
    }

    public function getVendor($vendorId)
    {
        return $this->vendorFactory->create()->load($vendorId);
    }

    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }
}
