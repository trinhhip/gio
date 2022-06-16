<?php


namespace OmnyfyCustomzation\VendorConfirm\Helper;


use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class Data extends AbstractHelper
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
     * @var PriceHelper
     */
    public $priceHelper;

    /**
     * Data constructor.
     * @param Context $context
     * @param Image $image
     * @param ProductFactory $productFactory
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        Context $context,
        Image $image,
        ProductFactory $productFactory,
        PriceHelper $priceHelper
    )
    {
        parent::__construct($context);
        $this->image = $image;
        $this->productFactory = $productFactory;
        $this->priceHelper = $priceHelper;
    }

    public function getThumbnailItem($product)
    {
        return $this->image->init($product, self::THUMBNAIL_PRODUCT_IMAGE_ID)
            ->resize(200, 200)
            ->getUrl();
    }

    public function getProductById($productId)
    {
        return $this->productFactory->create()->load($productId);
    }
    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}