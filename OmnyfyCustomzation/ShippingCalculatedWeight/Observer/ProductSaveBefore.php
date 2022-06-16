<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use OmnyfyCustomzation\ShippingCalculatedWeight\Helper\Data;

class ProductSaveBefore implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helperData;

    public function __construct(
        Data $helperData
    )
    {
        $this->helperData = $helperData;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    )
    {
        $product = $observer->getProduct();
        if ($product->getOverrideCsw()) {
            $product->setCalculatedShippingWeight($product->getOverrideCsw());
        } else {
            $calculatedShippingWeight = $this->helperData->getCalculatedShippingWeight($product);
            $product->setCalculatedShippingWeight($calculatedShippingWeight);
        }
    }
}