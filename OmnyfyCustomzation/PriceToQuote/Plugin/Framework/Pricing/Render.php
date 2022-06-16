<?php

namespace OmnyfyCustomzation\PriceToQuote\Plugin\Framework\Pricing;

use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Pricing\SaleableInterface;
use OmnyfyCustomzation\PriceToQuote\Helper\Data;


class Render
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Render constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param PricingRender $subject
     * @param callable $proceed
     * @param $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     */
    public function aroundRender(
        PricingRender $subject,
        callable $proceed,
        $priceCode,
        SaleableInterface $saleableItem,
        array $arguments = []
    )
    {
        if ($this->helper->isPriceToQuote($saleableItem)) {
            return $this->helper->getNewPriceHtmlBox($priceCode);
        }
        return $proceed($priceCode, $saleableItem, $arguments);
    }
}
