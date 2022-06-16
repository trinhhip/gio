<?php


namespace OmnyfyCustomzation\B2C\Plugin\Quote;


use Amasty\HidePrice\Helper\Data as HidePriceHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use OmnyfyCustomzation\B2C\Helper\Data as HelperData;

class QuotePlugin
{
    /**
     * @var HidePriceHelper
     */
    private $helper;
    /**
     * @var HelperData
     */
    private $helperData;

    public function __construct(
        HidePriceHelper $helper,
        HelperData $helperData
    )
    {
        $this->helper = $helper;
        $this->helperData = $helperData;
    }

    /**
     * @param Quote $subject
     * @param Product $product
     * @param $request
     * @param $processMode
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        Quote $subject,
        Product $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    )
    {
        if (!$subject instanceof \Amasty\RequestQuote\Model\Quote
            && $this->helper->getHideAddToCart()
            && $this->helper->isApplied($product)
            && !$product->getForRetail()
        ) {
            throw new LocalizedException(__('Adding to the cart is disabled'));
        }

        return [$product, $request, $processMode];
    }

}
