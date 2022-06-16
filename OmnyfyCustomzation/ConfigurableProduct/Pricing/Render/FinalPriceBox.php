<?php


namespace OmnyfyCustomzation\ConfigurableProduct\Pricing\Render;

use Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox as MagentoFinalPriceBox;

class FinalPriceBox extends MagentoFinalPriceBox
{
    public function getFinalArguments()
    {
        return [
            'display_label' => __('Starting from'),
            'price_id' => $this->getPriceId('product-price-'),
            'price_type' => 'finalPrice',
            'include_container' => true,
            'schema' => false
        ];
    }

    public function getConfigurableFinalPrice()
    {
        $product = $this->getSaleableItem();
        $finalArguments = $this->getFinalArguments();
        if ($product->getTypeId() == 'configurable' && $product->getPriceDisplay()) {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($childProducts as $childProduct) {
                if ($childProduct->getId() == $product->getPriceDisplay()) {
                    $product = $childProduct;
                    $finalArguments['display_label'] = null;
                    break;
                }
            }
        }
        $finalPriceModel = $product->getPriceInfo()->getPrice('final_price');
        return $this->renderAmount($finalPriceModel->getAmount(), $finalArguments);
    }
}
