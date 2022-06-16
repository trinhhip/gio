<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Model\Rules\Config;


class Surcharge implements \Magento\Framework\Option\ArrayInterface
{
    const PERCENTAGE = 1;
    const FIXED = 2;
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PERCENTAGE, 'label' => __('Apply as percentage of original')],
            ['value' => self::FIXED, 'label' => __('Apply as fixed amount')]
        ];
    }
}