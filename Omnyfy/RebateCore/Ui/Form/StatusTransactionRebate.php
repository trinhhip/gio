<?php

namespace Omnyfy\RebateCore\Ui\Form;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Omnyfy\RebateCore\Ui\Component\Form
 */
class StatusTransactionRebate implements ArrayInterface
{
    const NO_ACTION_STATUS = 0;

    const PENDING_STATUS = 1;

    const PROCESSING_STATUS = 2;

    const INVOICE_STATUS = 3;

    /*
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => $this::NO_ACTION_STATUS, 'label' => __('No Action')],
            ['value' => $this::PENDING_STATUS, 'label' => __('Pending')],
            ['value' => $this::PROCESSING_STATUS, 'label' => __('Processing')],
            ['value' => $this::INVOICE_STATUS, 'label' => __('Invoice')]
        ];
    }

    public function toArray()
    {
        return [
            $this::NO_ACTION_STATUS => __('No Action'),
            $this::PENDING_STATUS => __('Pending'),
            $this::PENDING_STATUS => __('Processing'),
            $this::INVOICE_STATUS => __('Invoice')
        ];
    }
}
