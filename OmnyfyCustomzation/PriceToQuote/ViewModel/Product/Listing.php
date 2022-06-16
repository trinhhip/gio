<?php


namespace OmnyfyCustomzation\PriceToQuote\ViewModel\Product;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Helper\Product\Compare;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Omnyfy\Vendor\Helper\Product;
use OmnyfyCustomzation\PriceToQuote\Helper\Data;

class Listing implements ArgumentInterface
{
    /**
     * @var Product
     */
    public $vendorHelper;
    /**
     * @var Output
     */
    public $outputHelper;
    /**
     * @var Image
     */
    public $imageHelper;
    /**
     * @var \Smartwave\Dailydeals\Helper\Data
     */
    public $dailyDealsHelper;
    /**
     * @var \Smartwave\Porto\Helper\Data
     */
    public $portoHelper;
    /**
     * @var StockRegistryInterface
     */
    public $stock;
    /**
     * @var Data
     */
    public $priceQuoteHelper;
    /**
     * @var Compare
     */
    public $compareHelper;
    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    public $wishlistHelper;
    /**
     * @var \OmnyfyCustomzation\B2C\Helper\Data
     */
    public $b2cHelper;

    public function __construct(
        Product $vendorHelper,
        Output $outputHelper,
        Image $imageHelper,
        \Smartwave\Dailydeals\Helper\Data $dailyDealsHelper,
        \Smartwave\Porto\Helper\Data $portoHelper,
        Compare $compareHelper,
        StockRegistryInterface $stock,
        Data $priceQuoteHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \OmnyfyCustomzation\B2C\Helper\Data $b2cHelper
    )
    {
        $this->vendorHelper = $vendorHelper;
        $this->outputHelper = $outputHelper;
        $this->imageHelper = $imageHelper;
        $this->dailyDealsHelper = $dailyDealsHelper;
        $this->portoHelper = $portoHelper;
        $this->stock = $stock;
        $this->priceQuoteHelper = $priceQuoteHelper;
        $this->compareHelper = $compareHelper;
        $this->wishlistHelper = $wishlistHelper;
        $this->b2cHelper = $b2cHelper;
    }

    public function getVendorHelper()
    {
        return $this->vendorHelper;
    }

    public function getOutputHelper()
    {
        return $this->outputHelper;
    }

    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    public function getDailyDealsHelper()
    {
        return $this->dailyDealsHelper;
    }

    public function getPortoHelper()
    {
        return $this->portoHelper;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function getCompareHelper()
    {
        return $this->compareHelper;
    }

    public function getWishlistHelper()
    {
        return $this->wishlistHelper;
    }

    public function getPriceQuoteHelper()
    {
        return $this->priceQuoteHelper;
    }

    public function getB2CHelper()
    {
        return $this->b2cHelper;
    }
}