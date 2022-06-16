<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Model;


use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use OmnyfyCustomzation\ShippingCalculatedWeight\Helper\Data;

class ShippingCalculate
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var CollectionFactory
     */
    public $productCollection;

    public function __construct(
        CollectionFactory $productCollection,
        Data $helperData
    )
    {
        $this->productCollection = $productCollection;
        $this->helperData = $helperData;
    }

    public function updateCalculatedShippingWeight()
    {
        $count = 0;
        $products = $this->productCollection->create()->addAttributeToSelect('*');
        foreach ($products as $product) {
            $overrideCsw = $product->getOverrideCsw();
            if ($overrideCsw) {
                $calculatedShippingWeight = $overrideCsw;
            } else {
                $calculatedShippingWeight = $this->helperData->getCalculatedShippingWeight($product);
            }
            $product->setData('calculated_shipping_weight', $calculatedShippingWeight);
            $product->getResource()->saveAttribute($product, 'calculated_shipping_weight');
            $count++;
        }
        return $count;
    }
}