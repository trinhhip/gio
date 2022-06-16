<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 30/8/19
 * Time: 12:27 pm
 */
namespace Omnyfy\Vendor\Model\Order\Pdf;

use Amasty\Orderattr\Model\ConfigProvider;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Order\Pdf\Traits\AbstractPdfTrait;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

class Invoice extends \Magento\Sales\Model\Order\Pdf\Invoice
{
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * @var \Omnyfy\Vendor\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Omnyfy\Vendor\Helper\Backend
     */
    protected $_helperBackend;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    use AbstractPdfTrait;
    /**
     * Invoice constructor.
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Config $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Omnyfy\Vendor\Helper\Product $productHelper
     * @param \Omnyfy\Vendor\Helper\Data $helper
     * @param \Omnyfy\Vendor\Helper\Backend $helperBackend
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Omnyfy\Vendor\Helper\Product $productHelper,
        \Omnyfy\Vendor\Helper\Data $helper,
        \Omnyfy\Vendor\Helper\Backend $helperBackend,
        \Magento\Framework\App\ResourceConnection $resource,
        FormFactory $metadataFormFactory,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->_productHelper = $productHelper;
        $this->_helper = $helper;
        $this->appEmulation = $appEmulation;
        $this->_helperBackend = $helperBackend;
        $this->_resource = $resource;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->entityResolver = $entityResolver;
        $this->configProvider = $configProvider;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $appEmulation,
            $data
        );
    }

    /**
     * @return bool
     */
    protected function isPrintAttributesAllowed()
    {
        return (bool)$this->configProvider->isIncludeToInvoicePdf();
    }

    protected function isInvoiceByMo()
    {
        $invoiceBy = $this->_scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_INVOICE_BY);
        return $invoiceBy == \Omnyfy\Vendor\Model\Config::INVOICE_BY_MO ? true : false;
    }

    protected function getMoAbn()
    {
        return $this->_scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_MO_ABN);
    }

    protected function getMoName()
    {
        return $this->_helper->getMoName();
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->_localeResolver->emulate($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());

            if ($this->isInvoiceByMo()) {
                /* Add table */
                $this->_drawHeader($page);
                /* Add body */
                foreach ($invoice->getAllItems() as $item) {
                    if ($item->getOrderItem()->getParentItem()) {
                        continue;
                    }
                    /* Draw item */
                    $this->_drawItem($item, $page, $order);
                    $page = end($pdf->pages);
                }
            }else {
                $_sources = $this->_helper->getSourceInfo($invoice->getAllItems());
                $this->y = $this->y ? $this->y : 815;
                $top = $this->y;
                $top += 10;
                foreach($_sources as $sourceKey => $_source) {
                    /* Add table */
                    $currentVendorId = $this->_helperBackend->getBackendVendorId();
                    if($currentVendorId && $_source->getVendorId() != $currentVendorId){
                        continue;
                    }
                    $vendorDetails = $this->_productHelper->getVendor($_source->getVendorId());
                    if (!empty($vendorDetails->getAbn())) {
//                        $page->drawText($vendorDetails->getName() . ' - ABN: ' . $vendorDetails->getAbn(), 225, $this->y, 'UTF-8');
                    }
//                    $page->drawText($vendorDetails->getName() . ' - ABN: ' . $vendorDetails->getAbn(), 225, $top - 15, 'UTF-8');

                    $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
                    $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
                    $page->setLineWidth(0.5);
                    $page->drawRectangle(25, $this->y, 570, $this->y - 25);
                    $this->y -= 15;
                    $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

                    //columns headers
                    $lines[0] = [];
                    $lines[0][] = ['text' => $vendorDetails->getName() . ' - '.$this->_helper->getTaxNumberByVendorId($vendorDetails->getId()).': ' . $vendorDetails->getAbn(), 'feed' => 35, 'font' => 'bold', 'font_size' => 12];

                    $lineBlock = ['lines' => $lines, 'height' => 15];

                    $this->drawLineBlocks($page, [$lineBlock]);
//                    $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                    $this->y -= 10;

                    $this->_drawHeader($page);

                    /* Add body */
                    foreach ($invoice->getAllItems() as $item) {
                        if ($item->getOrderItem() && $item->getOrderItem()->getParentItem()) {
                            continue;
                        }
                        if ($item->getSourceStockId() == $_source->getId()) {
                            /* Draw item */
                            $this->_drawItem($item, $page, $order);
                            $page = end($pdf->pages);
                        }
                    }
                }
            }

            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        if ($this->isPrintAttributesAllowed() && !$this->newPageHeader) {
            $this->isAlreadyDrawn = true;
            $orderAttributesData = [];
            $entity = $this->entityResolver->getEntityByOrder($this->currentOrder);
            $form = $this->createEntityForm($entity, $this->currentOrder->getStore());
            $outputData = $form->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML);
            foreach ($outputData as $attributeCode => $data) {
                if (!empty($data)) {
                    $orderAttributesData[] = [
                        'label' => $form->getAttribute($attributeCode)->getDefaultFrontendLabel(),
                        'value' => $this->resolveValue($data)
                    ];
                }
            }

            if (!empty($orderAttributesData)) {
                $this->drawOrderAttributesHeader($page);

                if ($lineBlocks = $this->createLinesBlockFromAttributes($page, $orderAttributesData)) {
                    foreach ($lineBlocks as $lineBlock) {
                        $page = $this->drawLineBlocks($page, [$lineBlock]);
                    }

                    $this->y -= 20;
                }

                if ($this->y < 80) {
                    $page = $this->newPage();
                }
            }
        }

        $this->lastPage = $page;

        $invoiceByMo = $this->isInvoiceByMo();

        if ($invoiceByMo) {
            return parent::_drawHeader($page);
        }

        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 290, 'align' => 'right'];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 435, 'align' => 'right'];

        $lines[0][] = ['text' => __('Price'), 'feed' => 375, 'align' => 'right'];

        $lines[0][] = ['text' => __('Tax'), 'feed' => 495, 'align' => 'right'];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->currentOrder = $order;

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
        }

        $invoiceByMo = $this->isInvoiceByMo();
        if ($invoiceByMo) {
            $page->drawText($this->getMoName(), 475, $top, 'UTF-8');
        }

        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top -= 15,
            'UTF-8'
        );

        if ($invoiceByMo) {
            $abn = $this->getMoAbn();
            $page->drawText(
                __('Tax Number: ') . $abn,
                475,
                $top,
                'UTF-8'
            );
        }

        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . __(
                    'Total Shipping Charges'
                ) . " " . $order->formatPriceTxt(
                    $order->getShippingAmount()
                ) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 30;
        }
    }

    /**
     * Insert totals to pdf page
     *
     * @param  \Zend_Pdf_Page $page
     * @param  \Magento\Sales\Model\AbstractModel $source
     * @return \Zend_Pdf_Page
     */
    protected function insertTotals($page, $source)
    {
        $vendorId = $this->_helperBackend->getBackendVendorId();
        $order = $source->getOrder();
        $totals = $this->_getTotalsList();
        $lineBlock = ['lines' => [], 'height' => 15];
        $invoiceId = $source->getId();
        $vendorInvoiceTotals = $this->getVendorInvoiceTotals($vendorId, $invoiceId);

        foreach ($totals as $total) {
            $total->setOrder($order)->setSource($source);

            if ($total->canDisplay()) {
                if($vendorId && $total->getData('source_field') == 'mcm_transaction_fee_incl_tax') {
                    continue;
                }

                $total->setFontSize(10);
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    if($vendorId && $total->getData('source_field') == 'subtotal') {
                        $lineBlock['lines'][] = [
                            [
                                'text' => $totalData['label'],
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => !empty($vendorInvoiceTotals['subtotal']) ?
                                    $this->convertAmount($order, number_format((float)$vendorInvoiceTotals['subtotal'], 2)) :
                                    $totalData['amount'],
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ],
                        ];
                        continue;
                    }
                    if($vendorId && $total->getData('source_field') == 'shipping_amount') {
                        $lineBlock['lines'][] = [
                            [
                                'text' => $totalData['label'],
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => !empty($vendorInvoiceTotals['shipping_amount']) ? $this->convertAmount($order, $vendorInvoiceTotals['shipping_amount']) : $totalData['amount'],
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ],
                        ];
                        continue;
                    }
                    if($vendorId && $total->getData('source_field') == 'grand_total') {
                        $lineBlock['lines'][] = [
                            [
                                'text' => $totalData['label'],
                                'feed' => 475,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold',
                            ],
                            [
                                'text' => !empty($vendorInvoiceTotals['grand_total']) ? $this->convertAmount($order, $vendorInvoiceTotals['grand_total']) : $totalData['amount'],
                                'feed' => 565,
                                'align' => 'right',
                                'font_size' => $totalData['font_size'],
                                'font' => 'bold'
                            ],
                        ];
                        continue;
                    }
                    $lineBlock['lines'][] = [
                        [
                            'text' => $totalData['label'],
                            'feed' => 475,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold',
                        ],
                        [
                            'text' => $totalData['amount'],
                            'feed' => 565,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold'
                        ],
                    ];
                }
            }
        }

        $this->y -= 20;
        $page = $this->drawLineBlocks($page, [$lineBlock]);
        return $page;
    }

    public function getVendorInvoiceTotals($vendorId, $invoiceId) {
        $adapter = $this->_resource->getConnection();
        $table = $adapter->getTableName('omnyfy_mcm_vendor_invoice');
        $select = $adapter->select()->from(
            $table, [
                'subtotal',
                'shipping_amount',
                'shipping_incl_tax',
                'grand_total',
                'base_grand_total'
            ]
        )->where(
            "vendor_id = ?", (int) $vendorId
        )->where(
            "invoice_id = ?", (int) $invoiceId
        );

        return $adapter->fetchRow($select);
    }

    public function convertAmount($order, $amount)
    {
        $amount = $order->formatPriceTxt($amount);
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        return $amount;

    }

}
