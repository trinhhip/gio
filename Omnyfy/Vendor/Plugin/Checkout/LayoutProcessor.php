<?php


namespace Omnyfy\Vendor\Plugin\Checkout;

class LayoutProcessor
{

    public function afterProcess($subject, $jsLayout)
    {
        foreach ($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                 ['shippingAddress'] as &$child)
        {
            if (isset($child['shipping-address-fieldset']['children']['city'])) {
                $child['shipping-address-fieldset']['children']['city'] = array_merge(
                    $child['shipping-address-fieldset']['children']['city'],
                    ['sortOrder' => 79]
                );
            }
            if (isset($child['shipping-address-fieldset']['children']['region_id'])) {
                $child['shipping-address-fieldset']['children']['region_id'] = array_merge(
                    $child['shipping-address-fieldset']['children']['region_id'],
                    ['sortOrder' => 81]
                );
            }
            if (isset($child['shipping-address-fieldset']['children']['postcode'])) {
                $child['shipping-address-fieldset']['children']['postcode'] = array_merge(
                    $child['shipping-address-fieldset']['children']['postcode'],
                    ['sortOrder' => 82]
                );
            }
        }

        return $jsLayout;
    }
}
