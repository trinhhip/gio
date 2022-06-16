<?php

namespace Omnyfy\Mcm\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class Fee
 * @package Omnyfy\Mcm\Model\Invoice\Total
 */
class TransactionFee extends AbstractTotal
{
    /*
     * @var \Omnyfy\Mcm\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Omnyfy\Mcm\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Omnyfy\Mcm\Helper\Data $helper,
        array $data = []
    ){
        $this->_helper = $helper;
        parent::__construct($data);
    }

    /**
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $orderData = $invoice->getOrder()->getData();
        if(!empty($orderData['base_total_invoiced']) && ($orderData['base_total_invoiced'] > 0)) {
            return $this;
        }

        $invoice->setMcmTransactionFee(0);
        $invoice->setMcmBaseTransactionFee(0);

  		$amount = $invoice->getOrder()->getMcmTransactionFee();
        $invoice->setMcmTransactionFee($amount);
        $amountBase = $invoice->getOrder()->getMcmBaseTransactionFee();
        $invoice->setMcmBaseTransactionFee($amountBase);

        $invoice->setMcmTransactionFeeTax(0);
		$invoice->setMcmTransactionFeeInclTax(0);

		$amountTax = $invoice->getOrder()->getMcmTransactionFeeTax();
        $invoice->setMcmTransactionFeeTax($amountTax);
		$amountInclTax = $invoice->getOrder()->getMcmTransactionFeeInclTax();
        $invoice->setMcmTransactionFeeInclTax($amountInclTax);

		$invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMcmTransactionFeeInclTax());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getMcmTransactionFeeInclTax());

        return $this;
    }
}
