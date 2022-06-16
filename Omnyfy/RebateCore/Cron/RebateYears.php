<?php

namespace Omnyfy\RebateCore\Cron;

use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Api\Data\ITransactionRebateRepository;
use Omnyfy\RebateCore\Api\Data\IRebateInvoiceRepository;
use Omnyfy\RebateCore\Helper\Data;
use Omnyfy\RebateCore\Model\Repository\VendorRebateRepository;
use Omnyfy\RebateCore\Ui\Form\StatusInvoiceRebate;
use Omnyfy\RebateCore\Ui\Form\StatusTransactionRebate;
use Omnyfy\RebateCore\Controller\Adminhtml\Invoice\Send;
use Omnyfy\RebateCore\Model\PDF\InvoicePdf;

/**
 * Class RebateYears
 * @package Omnyfy\RebateCore\Cron
 */
class RebateYears
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $vendorRebateCollection;

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
        IRebateInvoiceRepository $rebateInvoiceRepository,
        ITransactionRebateRepository $transactionRebate,
        Data $helper,
        \Omnyfy\RebateCore\Model\ResourceModel\VendorRebate\CollectionFactory $vendorRebateCollection,
        Send $sendEmail,
        InvoicePdf $invoicePdf
    )
    {
        $this->vendorRebateCollection = $vendorRebateCollection;
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
            $year = date('Y', strtotime($date));
            $endDate = date('Y-m-d 23:59:59', strtotime($date . " -1 day"));
            $rebateVendors = $this->vendorRebateCollection->create()->addFieldToFilter('lock_payment_frequency', ['eq' => PaymentFrequency::ANNUALLY_ON_SPECIFIC_DATE]);
            foreach ($rebateVendors as $rebateVendor) {
                $endDateRebate = date($year . '-m-d 23:59:59', strtotime($rebateVendor->getLockEndDate()));
                if (strtotime($endDate) == strtotime($endDateRebate)) {
                    $this->actionInvoiceRebate($rebateVendor->getId(), $rebateVendor->getVendorId());
                }          
            }
        }     
    }

    public function actionInvoiceRebate($vendorRebateId, $vendorId){
        $rebateTransactions = $this->transactionRebate->getVendorAnnualGroupOrder($vendorRebateId, $vendorId);
        foreach ($rebateTransactions as $rebateTransaction) {
            if (!empty($rebateTransaction['vendor_id'])) {
                $invoice = $this->rebateInvoiceRepository->getRebateInvoice();
                $rebateTransaction['status'] = StatusInvoiceRebate::PENDING_PAYMENT;
                $invoice->setData($rebateTransaction);
                $invoice = $this->rebateInvoiceRepository->saveRebateInvoice($invoice);
                $insertItems[] = $this->formatDataInvoiceItem($rebateTransaction);
                $this->rebateInvoiceRepository->insertValues($invoice->getId(), $insertItems);
                $this->sendEmailInvoice($invoice->getId());
            }
        }
        $this->transactionRebate->updateTransactionVendorAnnualOrder($vendorRebateId, $vendorId);
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
