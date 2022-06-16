<?php

namespace Omnyfy\RebateCore\Ui\Form;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Omnyfy\RebateCore\Ui\Component\Form
 */
class StatusInvoiceRebate implements ArrayInterface
{
    const PENDING_PAYMENT = 0;

    const PAID_STATUS = 1;

    const VOID_STATUS = 2;

    /*
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => $this::PENDING_PAYMENT, 'label' => __('Pending Payment')],
            ['value' => $this::PAID_STATUS, 'label' => __('Paid')],
            ['value' => $this::VOID_STATUS, 'label' => __('Void')]
        ];
    }

    public function toArray()
    {
        return [
            $this::PENDING_PAYMENT => __('Pending Payment'),
            $this::PAID_STATUS => __('Paid'),
            $this::VOID_STATUS => __('Void')
        ];
    }
}
