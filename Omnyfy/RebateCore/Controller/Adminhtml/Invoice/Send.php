<?php
namespace Omnyfy\RebateCore\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Omnyfy\RebateCore\Model\PDF\InvoicePdf;
use Omnyfy\RebateCore\Helper\Data;
use Omnyfy\RebateCore\Api\Data\IRebateInvoiceRepository;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Magento\Framework\Controller\ResultFactory; 

class Send extends Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    protected $invoicePdf;

    /**
     * @var IRebateInvoiceRepository
     */
    protected $rebateInvoiceRepository;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var helperRebate
     */
    protected $helperRebate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @param Context            $context
     */
    public function __construct(
        Action\Context $context,
        InvoicePdf $invoicePdf,
        IRebateInvoiceRepository $rebateInvoiceRepository,
        VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        Data $helperRebate,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->invoicePdf = $invoicePdf;
        $this->_localeDate = $localeDate;
        $this->helperRebate = $helperRebate;
        $this->rebateInvoiceRepository = $rebateInvoiceRepository;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context);
    }

    /**
     * to generate pdf
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        try {
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $pdf = $this->invoicePdf->getPdf($invoiceId);
            $fileName = "invoice_". $invoiceId .".pdf";
            $invoice = $this->rebateInvoiceRepository->getRebateInvoice($invoiceId);
            $this->sendEmailInvoice($invoice, $pdf->render(), $fileName);
            $this->messageManager->addSuccess(__('Invoice Rebate send email success.'));
            return $resultRedirect;
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect;
        }
    }

    /**
     * @param $vendorModel
     * @param $rebateModel
     * @param $percentageUp
     */
    public function sendEmailInvoice($invoice, $pdfFile, $fileName)
    {
        $marketPlaceName = $this->helperRebate->getStoreName();
        $vendor = $this->vendorRepository->getById($invoice->getVendorId());
        $paymentTerm = $this->helperRebate->getPaymentTerm() ?? 0;
        $duDate = date('Y-m-d', strtotime($invoice->getCreatedAt() . "+". $paymentTerm ." day"));
        $duDate = $this->_localeDate->formatDate(
            $this->_localeDate->scopeDate(
                0,
                $duDate,
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );
        $startDatePeriod = date('Y-m-d', strtotime($invoice->getCreatedAt() . "-1 day"));
        $endDatePeriod = date('Y-m-d', strtotime($invoice->getCreatedAt() . "-1 year"));
        $startDatePeriod = $this->_localeDate->formatDate(
            $this->_localeDate->scopeDate(
                0,
                $startDatePeriod,
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );
        $endDatePeriod = $this->_localeDate->formatDate(
            $this->_localeDate->scopeDate(
                0,
                $endDatePeriod,
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );
        $emailContact = $this->helperRebate->getStoreSupportEmail();
        $vars = [
            "maketplacename" => $marketPlaceName,
            "vendorname" => $vendor->getName(),
            "startPeriod" => $startDatePeriod,
            "endPeriod" => $endDatePeriod,
            "dueDate" => $duDate,
            "amountTax" => $invoice->getRebateTotalAmount(),
            "taxInclude" => number_format($invoice->getRebateTaxAmount(),2),
            "contact" => $emailContact
        ];
        $vars['paymentfrequency'] = ($invoice->getPaymentFrequency() == PaymentFrequency::MONTHLY_AT_END_OF_MONTH) ? __("Monthly") : __("Annual");
        $sendEmail = [
            "email" => $vendor->getEmail(),
            "name" => $vendor->getName()
        ];
        $this->helperRebate->sendEmailInvoice($pdfFile, $fileName, $vars, $sendEmail);
    }

} 