<?php

namespace Omnyfy\Mcm\Plugin\Model\Service;

class InvoiceService
{
    private $vendorHelper;

    private $quoteFactory;

    public function __construct(
        \Omnyfy\Vendor\Helper\Data $vendorHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->vendorHelper = $vendorHelper;
        $this->quoteFactory = $quoteFactory;
    }
    public function afterPrepareInvoice(
        \Magento\Sales\Model\Service\InvoiceService $subject,
        $result,
        $order,
        $orderItemsQtyToInvoice
    ) {
        $invoice = $result;
        if($invoice->getOrder()->getShippingAmount() == 0){
            return $result;
        }
        $invoiceTotalData = $this->vendorHelper->vendorInvoiceTotalData($invoice);
        $totalData = [];
        foreach($invoiceTotalData as $vendorInvoiceTotal){
            foreach ($vendorInvoiceTotal as $col => $value){
                if(!empty($totalData[$col])){
                    $totalData[$col] += $value;
                }else{
                    $totalData[$col] = $value;
                }
            }
        }
        $excludedDataArray = [
            'vendor_id',
            'invoice_id'
        ];
        foreach ($totalData as $col => $value){
            if(in_array($col, $excludedDataArray)){
                continue;
            }
            $invoice->setData($col, $value);
        }
        $transactionFeeAdded = false;
        // Check if previous invoice already calculate shipping amount for current method
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ((double)$previousInvoice->getShippingAmount() && !$previousInvoice->isCanceled() && $previousInvoice->getId()) {
                $allPreviousInvoiceItem = $previousInvoice->getAllItems();
                if(!empty($allPreviousInvoiceItem)){
                    $transactionFeeAdded = true;
                }
            }
        }

        if(!$transactionFeeAdded){
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMcmTransactionFeeInclTax());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getMcmTransactionFeeInclTax());
        }
        return $invoice;
    }
}
