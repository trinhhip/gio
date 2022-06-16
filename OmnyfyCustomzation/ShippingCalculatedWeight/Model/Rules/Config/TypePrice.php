<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Model\Rules\Config;


class TypePrice implements \Magento\Framework\Option\ArrayInterface
{
    const FORMULA = 1;
    const FIXED = 2;
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FORMULA, 'label' => __('Formula')],
            ['value' => self::FIXED, 'label' => __('Fixed')]
        ];
    }
}