<?php

namespace Omnyfy\RebateCore\Ui\Form;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Omnyfy\RebateUI\Ui\Component\Form
 */
class CalculationBased implements ArrayInterface
{
    /**
     *
     */
    const VENDOR_ORDER_SUBTOTAL = 4;
    /**
     *
     */
    const TOTAL_ORDER_VALUE_ABOVE_THRESHOLD = 5;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => $this::VENDOR_ORDER_SUBTOTAL, 'label' => __('Vendor Order Subtotal (incl Tax) ')],
            ['value' => $this::TOTAL_ORDER_VALUE_ABOVE_THRESHOLD, 'label' => __('Total Order Value above Threshold')]
        ];
    }
}
