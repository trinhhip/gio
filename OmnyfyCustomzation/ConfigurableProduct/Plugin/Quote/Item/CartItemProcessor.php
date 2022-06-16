<?php


namespace OmnyfyCustomzation\ConfigurableProduct\Plugin\Quote\Item;


use Magento\Quote\Api\Data\CartItemInterface;

class CartItemProcessor
{
    public function aroundProcessOptions(
        \Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor $subject,
        \Closure $proceed,
        CartItemInterface $cartItem
    )
    {
        $attributesOption = $cartItem->getProduct()->getCustomOption('attributes');

        if (!$attributesOption) {
            $product = $cartItem->getProduct();
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cart.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(__('Name: %1 - SKU: %2', $product->getName(), $product->getSku()));
            return $cartItem;
        }
        return $proceed($cartItem);
    }
}
