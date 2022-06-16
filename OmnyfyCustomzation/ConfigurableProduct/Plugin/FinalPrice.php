<?php


namespace OmnyfyCustomzation\ConfigurableProduct\Plugin;


use Magento\Catalog\Block\Product\ListProduct;

class FinalPrice
{
    public function beforeGetProductPrice(ListProduct $subject, $product)
    {
        if ($product->getTypeId() == 'configurable' && $product->getPriceDisplay()) {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($childProducts as $key => $childProduct) {
                if ($childProduct->getId() == $product->getPriceDisplay()) {
                    return [$childProduct];
                }
            }
        }
        return [$product];
    }
}
