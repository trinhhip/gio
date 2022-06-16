<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\RebateCore\Model\PDF;
use Omnyfy\RebateCore\Api\Data\IRebateInvoiceRepository;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\RebateCore\Helper\Data as HelperCore;
use Magento\MediaStorage\Helper\File\Storage\Database;

/**
 * Sales Order Invoice PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoicePdf extends AbstractPdf
{
    /**
     * @var IRebateInvoiceRepository
     */
    protected $rebateInvoiceRepository;

    /**
     * @var \Omnyfy\RebateUI\Helper\Data
     */
    protected $helperRebateUI;

    /**
     * @var IVendorRebateRepository
     */
    protected $vendorRebateRepository;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var HelperCore
     */
    protected $helperCore;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var Database
     */
    private $fileStorageDatabase;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Filesystem $filesystem,
        IRebateInvoiceRepository $rebateInvoiceRepository,
        IVendorRebateRepository $vendorRebateRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        HelperCore $helperCore,
        \Omnyfy\RebateUI\Helper\Data $helperRebateUI,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        Database $fileStorageDatabase = null,
        array $data = []
    ) {
        $this->rebateInvoiceRepository = $rebateInvoiceRepository;
        $this->vendorRebateRepository = $vendorRebateRepository;
        $this->vendorRepository = $vendorRepository;
        $this->_localeDate = $localeDate;
        $this->helperRebateUI = $helperRebateUI;
        $this->helperCore = $helperCore;
        $this->_scopeConfig = $scopeConfig;
        $this->fileStorageDatabase = $fileStorageDatabase ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(Database::class);
        parent::__construct($paymentData, $string, $filesystem, $inlineTranslation, $data);
    }

    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Rebate Name'), 'feed' => 30];

        $lines[0][] = ['text' => __('Amount (Incl Tax)'), 'feed' => 375, 'align' => 'right'];

        $lines[0][] = ['text' => __('Tax included'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($invoiceId)
    {
        $invoice = $this->rebateInvoiceRepository->getRebateInvoice($invoiceId);
        $this->_beforeGetPdf();
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        $page = $this->newPage();
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(255, 255, 255));
        $page->drawRectangle(15, $this->y + 25, 580, $this->y - 790);
        $this->insertLogo($page);
        $this->insertMarketPlaceName($page);
        $this->insertInvoiceForm($page);
        $this->insertInvoiceTo($page, $invoice);
        $this->insertTaxInvoice($page, $invoice);
        $this->_drawHeader($page);
        $this->insertRebate($page, $invoiceId);
        $this->insertFooter($page);
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Insert logo to pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Zend_Pdf_Exception
     */
    protected function insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($image) {
            $imagePath = '/sales/store/logo/' . $image;
            if ($this->fileStorageDatabase->checkDbUsage() &&
                !$this->_mediaDirectory->isFile($imagePath)
            ) {
                $this->fileStorageDatabase->saveFileToFilesystem($imagePath);
            }
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                $top = 800;
                //top border of the page
                $widthLimit = 50;
                //half of the page width
                $heightLimit = 50;
                //assuming the image is not a "skyscraper"
                $width = 50;
                $height = 50;

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }
    }

    /**
     *
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return void
     */
    protected function insertMarketPlaceName(&$page)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 20);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 10;
        $marketPlaceName = $this->helperCore->getStoreName();
        $page->drawText(trim($marketPlaceName), 30, $this->y - $top, 'UTF-8');
        $this->y -= 10;
        $this->y;
    }

    protected function insertFooter(\Zend_Pdf_Page $page)
    {
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y-25, 570, $this->y - 200);
        $this->y -= 40;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->drawText(__('Payment Details'), 30, $this->y, 'UTF-8');
        $this->y -= 20;
        $content = $this->helperCore->getPaymentDetail();
        $contentArr = explode("\n",$content);
        foreach ($contentArr as $text) {
            $page->drawText($text, 30, $this->y, 'UTF-8');
            $this->y -= 10;
        }
        
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
    }

    /**
     * Insert address to pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return void
     */
    protected function insertRebate(&$page, $invoiceId)
    {
        $invoiceItems = $this->rebateInvoiceRepository->loadInvoiceItemByRebate($invoiceId);
        $this->_setFontRegular($page, 10);
        $totalTax = 0;
        $totalTotal = 0;
        foreach ($invoiceItems as $invoiceItem) {
            $lines = [];
            $totalTax += $invoiceItem['rebate_tax_amount'];
            $totalTotal += $invoiceItem['rebate_total_amount'];
            $vendorRebate = $this->vendorRebateRepository->getRebateVendor($invoiceItem['vendor_rebate_id']);
            $rebateName = $vendorRebate->getLockName();
            $lines[0][] = ['text' => $this->string->split($rebateName, 60, true, true), 'feed' => 30];

            $lines[0][] = ['text' => $this->helperRebateUI->formatToBaseCurrency($invoiceItem['rebate_total_amount']), 'feed' => 375, 'align' => 'right'];

            $lines[0][] = ['text' => $this->helperRebateUI->formatToBaseCurrency($invoiceItem['rebate_tax_amount']), 'feed' => 565, 'align' => 'right'];
            $lineBlock = ['lines' => $lines, 'height' => 15];
            $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        }
        $this->y -= 5;
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y);
        $this->y -= 5;
        $this->_setFontBold($page, 14);
        $lines = [];
        $lines[0][] = ['text' => __("Total Rebate Payable"), 'feed' => 200];
        $lines[0][] = ['text' => $this->helperRebateUI->formatToBaseCurrency($totalTotal), 'feed' => 375, 'align' => 'right'];

        $lines[0][] = ['text' => $this->helperRebateUI->formatToBaseCurrency($totalTax), 'feed' => 565, 'align' => 'right'];
        $lineBlock = ['lines' => $lines, 'height' => 5];
        $this->y -= 10;
        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
    }

    /**
     * Insert address to pdf page
     *
     * @param \Zend_Pdf_Page $page
     * @param string|null $store
     * @return void
     */
    protected function insertInvoiceTo(&$page, $invoice)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        // $top = 30;
        // $this->y -= $top;
        $padding = 10;
        $page->drawText("Invoice To:", 30, $this->y + 30, 'UTF-8');
        $this->y -= $padding;
        $vendor = $this->vendorRepository->getById($invoice->getVendorId());
        $page->drawText($vendor->getName(), 30, $this->y + 30, 'UTF-8');
        $this->y -= $padding;
        $page->drawText($vendor->getAddress(), 30, $this->y + 30, 'UTF-8');
        $this->y -= 20;
    }

    protected function insertInvoiceForm(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(250, $this->y -20, 570, $this->y - 80);
        $this->y -= 40;
        $marketPlaceName = $this->helperCore->getStoreName();
        $taxNumber = $this->helperCore->getStoreVAT();
        $marketPlaceAddess = $this->helperCore->getStoreAddress();
        //columns headers
        $lines[] = __('Invoice Form');

        $lines[] = $marketPlaceName;

        $lines[] = $taxNumber;

        $lines[] = $marketPlaceAddess;
        foreach ($lines as $line) {
            $this->insertRightText($page, $line);
        }
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
    }

    protected function insertTaxInvoice(\Zend_Pdf_Page $page, $invoice)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $top = 30;
        $this->y -= $top;
        $padding = 10;
        $title = ($invoice->getPaymentFrequency() == PaymentFrequency::MONTHLY_AT_END_OF_MONTH) ? __("Monthly Rebates Tax Invoice") : __("Annual Rebates Tax Invoice");
        $page->drawText($title, 30, $this->y, 'UTF-8');
        $this->y -= $padding;
        $invoiceNumber = $invoice->getInvoiceNumber();
        $page->drawText(__("Rebate Tax Invoice Number: %1", $invoiceNumber), 30, $this->y, 'UTF-8');
        $this->y -= $padding;
        $paymentTerm = $this->helperCore->getPaymentTerm() ?? 0;
        $duDate = date('Y-m-d', strtotime($invoice->getCreatedAt() . "+". $paymentTerm ." day"));
        $createAt = $this->_localeDate->formatDate(
            $this->_localeDate->scopeDate(
                0,
                $invoice->getCreatedAt(),
                true
            ),
            \IntlDateFormatter::MEDIUM,
            false
        );
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
        $page->drawText(__("Invoice Date: %1", $createAt), 30, $this->y, 'UTF-8');
        $this->y -= $padding;
        $page->drawText(__("Invoice Due Date: %1", $duDate), 30, $this->y, 'UTF-8');
        if ($invoice->getPaymentFrequency() == PaymentFrequency::ANNUALLY_ON_SPECIFIC_DATE) {
            $this->y -= $padding;
            $page->drawText(__("Invoice Period: %1 - %2", $startDatePeriod, $endDatePeriod ), 30, $this->y, 'UTF-8');
        }
        $this->y -= $top;
    }


    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(\Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
