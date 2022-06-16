<?php

namespace Omnyfy\RebateCore\Cron;

use Omnyfy\RebateCore\Ui\Form\PaymentFrequency; 
use Omnyfy\RebateCore\Helper\Data;
use Omnyfy\RebateCore\Api\Data\ITransactionRebateRepository;
use Omnyfy\RebateCore\Api\Data\IRebateInvoiceRepository;
use Omnyfy\RebateCore\Ui\Form\StatusInvoiceRebate;
use Omnyfy\RebateCore\Ui\Form\StatusTransactionRebate;
use Omnyfy\RebateCore\Controller\Adminhtml\Invoice\Send;
use Omnyfy\RebateCore\Model\PDF\InvoicePdf;

/**
 * Class RebateMonth
 * @package Omnyfy\RebateCore\Cron
 */
class RebateMonth
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $transactionRebate;

    protected $rebateInvoiceRepository;

    protected $sendEmail;

    protected $invoicePdf;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * RebateMonth constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param IInvoiceRebateCalculateRepository $rebateCalculateInvoiceRepository
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Data $helper,
        IRebateInvoiceRepository $rebateInvoiceRepository,
        ITransactionRebateRepository $transactionRebate,
        Send $sendEmail,
        InvoicePdf $invoicePdf
    )
    {
        $this->transactionRebate = $transactionRebate;
        $this->rebateInvoiceRepository = $rebateInvoiceRepository;
        $this->date = $date;
        $this->helper = $helper;
        $this->sendEmail = $sendEmail;
        $this->invoicePdf = $invoicePdf;
    }

    /**
     *
     */
    public function execute()
    {
        if ($this->helper->isEnable()) {
            $date = $this->date->gmtDate();
            $createAt = date('Y-m-01', strtotime($date . " -1 month"));
            $rebateTransactions = $this->transactionRebate->getVendorMonthGroupOrder();
            foreach ($rebateTransactions as $rebateTransaction) {
                $invoice = $this->rebateInvoiceRepository->getRebateInvoice();
                $rebateTransaction['status'] = StatusInvoiceRebate::PENDING_PAYMENT;
                $invoice->setData($rebateTransaction);

                $invoice = $this->rebateInvoiceRepository->saveRebateInvoice($invoice);
                $items = $this->transactionRebate->getVendorMonthOrderTotal($invoice->getVendorId());
                $insertItems = [];
                if (!empty($items)) {
                    foreach ($items as $item) {
                        $insertItems[] = $this->formatDataInvoiceItem($item);
                    }
                }
                $this->rebateInvoiceRepository->insertValues($invoice->getId(), $insertItems);
                $this->transactionRebate->updateTransactionVendorMonthOrder($invoice->getVendorId());
                $this->sendEmailInvoice($invoice->getId());
            }
        }     
    }

    public function formatDataInvoiceItem($item){
        $data['vendor_rebate_id'] = $item['vendor_rebate_id'];
        $data['rebate_total_amount'] = $item['rebate_total_amount'];
        $data['rebate_net_amount'] = $item['rebate_net_amount'];
        $data['rebate_tax_amount'] = $item['rebate_tax_amount'];
        return $data;
    }

    public function sendEmailInvoice($invoiceId)
    {
        $pdf = $this->invoicePdf->getPdf($invoiceId);
        $fileName = "invoice_". $invoiceId .".pdf";
        $invoice = $this->rebateInvoiceRepository->getRebateInvoice($invoiceId);
        $this->sendEmail->sendEmailInvoice($invoice, $pdf->render(), $fileName);
    }
}
