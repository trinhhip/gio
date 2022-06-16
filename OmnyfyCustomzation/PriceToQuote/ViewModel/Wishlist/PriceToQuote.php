<?php


namespace OmnyfyCustomzation\PriceToQuote\ViewModel\Wishlist;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class PriceToQuote implements ArgumentInterface
{
    /**
     * @var \OmnyfyCustomzation\PriceToQuote\Helper\Data
     */
    public $helperData;

    public function __construct(
        \OmnyfyCustomzation\PriceToQuote\Helper\Data $helperData
    )
    {
        $this->helperData = $helperData;
    }

    public function isPriceToQuote($product)
    {
        return $this->helperData->isPriceToQuote($product);
    }

    public function getEmailUsBox($product)
    {
        return $this->helperData->getEmailUsBox($product->getId());
    }
}
