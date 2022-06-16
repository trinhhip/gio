<?php
namespace Omnyfy\RebateCore\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Helper\Data as RebateCoreHelper;

class Download extends Action
{

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    protected $invoiceRepository;

    protected $pdfHelper;

    protected $layoutFactory;

    protected $dateTime;

    protected $directoryList;

    protected $file;

    protected $rebateInvoiceRepository;

    protected $mcmHelper;

    protected $vendorRepository;

    protected $rebateCoreHelper;

    protected $vendorRebateRepository;
    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    private $logo;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Omnyfy\Core\Helper\DomPdfInterface $pdfHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Omnyfy\RebateCore\Api\Data\IRebateInvoiceRepository $rebateInvoiceRepository,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        RebateCoreHelper $rebateCoreHelper,
        \Omnyfy\RebateCore\Api\Data\IVendorRebateRepository $vendorRebateRepository,
        \Magento\Theme\Block\Html\Header\Logo $logo
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->pdfHelper = $pdfHelper;
        $this->layoutFactory = $layoutFactory;
        $this->dateTime = $dateTime;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->_fileFactory = $fileFactory;
        $this->rebateInvoiceRepository = $rebateInvoiceRepository;
        $this->mcmHelper = $mcmHelper;
        $this->vendorRepository = $vendorRepository;
        $this->_localeDate = $localeDate;
        $this->rebateCoreHelper = $rebateCoreHelper;
        $this->vendorRebateRepository = $vendorRebateRepository;

        parent::__construct($context);
        $this->logo = $logo;
    }

    /**
     * to generate pdf
     *
     * @return void
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = $this->rebateInvoiceRepository->getRebateInvoice($invoiceId);
            $htmlContent = $this->generateRebateHtml($invoice, $invoiceId);
            $this->pdfHelper->setData($htmlContent);

            $date = $this->dateTime->date('Y-m-d_H-i-s');
            $fileName = 'rebate' . $date . '.pdf';
            $filePath = $this->directoryList->getPath(DirectoryList::MEDIA)."/rebate/";
            if ( ! file_exists($filePath)) {
                $this->file->mkdir($filePath);
            }

            $this->saveFile($filePath . $fileName);
            $this->downloadFile($fileName);
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }

    public function generateRebateHtml($invoice, $invoiceId)
    {

        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layoutFactory->create()->createBlock('Magento\Framework\View\Element\Template');
        $block->setTemplate('Omnyfy_RebateCore::rebate/pdf_rebate.phtml');

        $data = [
            'rebate_data' => $this->getRebateData($invoice),
            'invoice_to' => $this->invoiceTo($invoice),
            'invoice_from' => $this->mcmHelper->getInvoiceFromData(),
            'invoice_items' => $this->invoiceItems($invoiceId),
            'total_rebate' => $this->totalRebate($invoiceId),
            'payment_detail' => $this->paymentDetail(),
            'logo_url' => $this->logo->getLogoSrc()
        ];

        $block->setData($data);

        return $block->toHtml();
    }

    protected function saveFile($filePath)
    {
        file_put_contents($filePath, $this->pdfHelper->save());
    }

    protected function downloadFile($fileName)
    {
        try {
            //Download
            $this->_fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => 'media/rebate/'.$fileName,
                    'rm' => 1
                ],
                DirectoryList::PUB,
                'application/pdf'
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("Error file download"));
        }
    }

    protected function getRebateData($invoice)
    {
        $title = ($invoice->getPaymentFrequency() == PaymentFrequency::MONTHLY_AT_END_OF_MONTH) ? __("Monthly Rebate") : __("Annual Rebate");
        $titleTaxInvoice = ($invoice->getPaymentFrequency() == PaymentFrequency::MONTHLY_AT_END_OF_MONTH) ? __("Monthly Rebates Tax Invoice") : __("Annual Rebates Tax Invoice");

        $data = [
            'title' => $title,
            'mo_name' => $this->mcmHelper->getMoName(),
            'logo_src' => $this->mcmHelper->getLogoSrc(),
            'title_invoice' => $titleTaxInvoice,
            'invoice_tax' => $invoice->getInvoiceNumber(),
            'invoice_date' => $this->invoiceDate($invoice),
            'invoice_due' => $this->invoiceDue($invoice),
            'invoice_period' => $this->invoicePeriod($invoice),
        ];

        return $data;
    }

    protected function invoiceTo($invoice)
    {
        $vendor = $this->vendorRepository->getById($invoice->getVendorId());

        return [
            'name' => $vendor->getName(),
            'address' => $vendor->getAddress(),
        ];
    }

    protected function invoiceDate($invoice)
    {
        $createAt = $this->_localeDate->formatDate(
            $this->_localeDate->scopeDate(
                0,
                $invoice->getCreatedAt(),
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );

        return $createAt;
    }

    protected function invoiceDue($invoice)
    {
        $paymentTerm = $this->rebateCoreHelper->getPaymentTerm() ?? 0;
        $dueDate = date('Y-m-d', strtotime($invoice->getCreatedAt() . "+". $paymentTerm ." day"));

        $dueDateFormat = $this->_localeDate->formatDate(
            $this->_localeDate->scopeDate(
                0,
                $dueDate,
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );

        return $dueDateFormat;
    }

    protected function invoicePeriod($invoice)
    {
        if ($invoice->getPaymentFrequency() == PaymentFrequency::ANNUALLY_ON_SPECIFIC_DATE) {
            $startDatePeriod = date('Y-m-d', strtotime($invoice->getCreatedAt() . "-1 day"));
            $endDatePeriod = date('Y-m-d', strtotime($invoice->getCreatedAt() . "-1 year"));
            $startDatePeriodFormat = $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    0,
                    $startDatePeriod,
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            );
            $endDatePeriodFormat = $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    0,
                    $endDatePeriod,
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            );

            return $startDatePeriodFormat . ' - ' . $endDatePeriodFormat;
        }
        return null;
    }

    protected function totalRebate($invoiceId)
    {
        $invoiceItems = $this->rebateInvoiceRepository->loadInvoiceItemByRebate($invoiceId);

        $totalAmount = 0;
        $totalTax = 0;
        foreach ($invoiceItems as $invoiceItem) {
            $totalAmount += $invoiceItem['rebate_total_amount'];
            $totalTax += $invoiceItem['rebate_tax_amount'];
        }

        return [
            'total_amount' => $totalAmount,
            'total_tax' => $totalTax
        ];
    }

    protected function invoiceItems($invoiceId)
    {
        $items = $this->rebateInvoiceRepository->loadInvoiceItemByRebate($invoiceId);

        $rebateItems = [];
        foreach ($items as $item) {
            $vendorRebate = $this->vendorRebateRepository->getRebateVendor($item['vendor_rebate_id']);
            $rebateName = $vendorRebate->getLockName();

            $rebateItems[] = [
                'vendor_rebate_id' => $item['vendor_rebate_id'],
                'rebate_name' => $rebateName,
                'rebate_total_amount' => $item['rebate_total_amount'],
                'rebate_tax_amount' => $item['rebate_tax_amount'],
            ];
        }

        return $rebateItems;
    }

    protected function paymentDetail()
    {
        $content = $this->rebateCoreHelper->getPaymentDetail();
        if ($content) {
            return explode("\n",$content);
        }
        return null;
    }

}
