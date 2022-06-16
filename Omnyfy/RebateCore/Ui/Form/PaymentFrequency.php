<?php

namespace Omnyfy\RebateCore\Ui\Form;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Omnyfy\RebateCore\Ui\Component\Form
 */
class PaymentFrequency implements ArrayInterface
{
    /**
     *
     */
    const PER_ORDER_SETTLEMENT = 1;
    /**
     *
     */
    const MONTHLY_AT_END_OF_MONTH = 2;
    /**
     *
     */
    const ANNUALLY_ON_SPECIFIC_DATE = 3;

    /*
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => $this::PER_ORDER_SETTLEMENT, 'label' => __('Per Order Settlement')],
            ['value' => $this::MONTHLY_AT_END_OF_MONTH, 'label' => __('Monthly at end of Month')],
            ['value' => $this::ANNUALLY_ON_SPECIFIC_DATE, 'label' => __('Annually on specific date')]
        ];
    }

    public function toArray()
    {
        return [
            $this::PER_ORDER_SETTLEMENT => __('Per Order Settlement'),
            $this::MONTHLY_AT_END_OF_MONTH => __('Monthly at end of Month'),
            $this::ANNUALLY_ON_SPECIFIC_DATE => __('Annually on specific date')
        ];
    }
}
