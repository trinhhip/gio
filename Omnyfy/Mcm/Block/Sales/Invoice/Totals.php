<?php

namespace Omnyfy\Mcm\Block\Sales\Invoice;

use Magento\Sales\Model\Order\Creditmemo;

/**
 * Class Totals
 * @package Omnyfy\Mcm\Block\Sales
 */
class Totals extends \Magento\Sales\Block\Order\Invoice\Totals {

    /**
     * @var \Omnyfy\Mcm\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Omnyfy\Mcm\Helper\Data $helper
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Omnyfy\Mcm\Helper\Data $helper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->_helper = $helper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->_currency = $currency;
    }



    /**
     * @var Creditmemo|null
     */
    protected $_creditmemo = null;


    public function getCurrencySymbol() {
        return $this->_currency->getCurrencySymbol();
    }

    public function initTotals() {

        if ($this->_helper->isTransactionFeeEnable() && $this->_helper->isEnable() && ($this->getSource()->getMcmTransactionFeeInclTax() > 0)) {
            $total = $this->dataObjectFactory->create()->setData(
                [
                    'code' => 'mcm_transaction_fee',
                    'value' => $this->getSource()->getMcmTransactionFeeInclTax(),
                    'base_value' => $this->getSource()->getMcmBaseTransactionFeeInclTax(),
                    'label' => 'Transaction Fee',
                ]
            );
            $this->getParentBlock()->addTotalBefore($total, 'grand_total');

            return $this;
        }
    }

    public function getInvoice()
    {
        if ($this->hasData('invoice')) {
            $this->_invoice = $this->_getData('invoice');
        } elseif ($this->_coreRegistry->registry('current_invoice')) {
            $this->_invoice = $this->_coreRegistry->registry('current_invoice');
        } elseif ($this->getParentBlock()->getInvoice()) {
            $this->_invoice = $this->getParentBlock()->getInvoice();
        }
        return $this->_invoice;
    }


}
