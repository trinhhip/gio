<?php

namespace Omnyfy\RebateCore\Observer;

use Magento\Framework\Exception\LocalizedException;
use Omnyfy\RebateCore\Helper\Data;

/**
 * Class InvoiceRebateAfterSave
 * @package Omnyfy\RebateCore\Observer
 */
class InvoiceRebateAfterSave implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Default pattern for Sequence
     */
    const DEFAULT_PATTERN  = "%s%'.09d";
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var string
     */
    private $pattern;

    /**
     * InvoiceRebateAfterSave constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper,
        $pattern = self::DEFAULT_PATTERN
    )
    {
        $this->helper = $helper;
        $this->pattern = $pattern;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoiceRebate = $observer->getData('invoiceRebate');
        $preFix = $this->helper->getPreFixInvoice();
        $invoiceId = $invoiceRebate->getId();
        $numberRebate = sprintf(
            $this->pattern,
            $preFix,
            $invoiceId
        );
        $invoiceRebate->setInvoiceNumber($numberRebate);
        $invoiceRebate->save();
    }
}
 