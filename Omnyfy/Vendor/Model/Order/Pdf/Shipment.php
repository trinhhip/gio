<?php
namespace Omnyfy\Vendor\Model\Order\Pdf;

use Amasty\Orderattr\Model\ConfigProvider;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Order\Pdf\Traits\AbstractPdfTrait;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use Magento\Sales\Model\RtlTextHandler;

class Shipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    const SHIPMENT_BY_MO = 1;
    /**
     * @var \Omnyfy\Vendor\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    protected $_helper;

    protected $_productRepository;

    protected $_vendorFactory;

    protected $_resource;

    private $_localeResolver;

    /**
     * @var RtlTextHandler
     */
    private $rtlTextHandler;

    use AbstractPdfTrait;

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
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Omnyfy\Vendor\Helper\Product $productHelper,
        \Omnyfy\Vendor\Helper\Data $helper,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        FormFactory $metadataFormFactory,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider,
        array $data = [],
        ?RtlTextHandler $rtlTextHandler = null
    )
    {
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->_productHelper = $productHelper;
        $this->_helper = $helper;
        $this->_productRepository = $productRepository;
        $this->_vendorFactory = $vendorFactory;
        $this->_resource = $resource;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->entityResolver = $entityResolver;
        $this->configProvider = $configProvider;
        $this->rtlTextHandler = $rtlTextHandler ?: ObjectManager::getInstance()->get(RtlTextHandler::class);
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $storeManager, $appEmulation, $data);
    }

    /**
     * @return bool
     */
    protected function isPrintAttributesAllowed()
    {
        return (bool)$this->configProvider->isIncludeToShipmentPdf();
    }

    protected function isShipmentByMo()
    {
        $shipmentBy = $this->_scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_SHIPMENT_BY);
        return $shipmentBy == self::SHIPMENT_BY_MO ? true : false;
    }

    protected function getMoAbn()
    {
        return $this->_scopeConfig->getValue(\Omnyfy\Vendor\Model\Config::XML_PATH_MO_ABN);
    }

    protected function getMoName()
    {
        return $this->_helper->getMoName();
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
            $top +=15;
        }

        $top -=30;
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
            $top,
            'UTF-8'
        );

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
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($order->getShippingAddress(), 'pdf')
            );
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
                    $text[] = $this->rtlTextHandler->reverseRtlText($_value);
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
                        $text[] = $this->rtlTextHandler->reverseRtlText($_value);
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
            $page->drawText(__('Payment Method:'), 35, $this->y, 'UTF-8');
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
            $quoteShipping = $this->_vendorFactory->create()->getResource()->getQuoteShipping($order->getQuoteId());
            $amount = 0;
            if ($quoteShipping) {
                foreach ($shipment->getAllItems() as $item) {
                    if ($item->getOrderItem() && $item->getOrderItem()->getParentItem()) {
                        continue;
                    }
                    foreach ($quoteShipping as $quote) {
                        if ($quote['vendor_id'] == $item->getVendorId()
                            && $quote['source_stock_id'] == $shipment->getData('source_stock_id')) {
                            $page->drawText(strip_tags(trim($quote['carrier'].' - '.$quote['method_title'])), 285, $this->y, 'UTF-8');
                            $this->y -= 15;
                            $amount = $quote['amount'];
                        }
                    }
                }
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "("
                . __('Total Shipping Charges')
                . " "
                . $order->formatPriceTxt($amount)
                . ")";

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
            $this->y -= 15;
        }
    }

    /**
     * Return PDF document
     *
     * @param \Magento\Sales\Model\Order\Shipment[] $shipments
     * @return \Zend_Pdf
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                $this->_localeResolver->emulate($shipment->getStoreId());
                $this->_storeManager->setCurrentStore($shipment->getStoreId());
            }

            if ($this->isShipmentByMo()) {
                $page = $this->newPage();
                $order = $shipment->getOrder();
                /* Add image */
                $this->insertLogo($page, $shipment->getStore());
                /* Add address */
                $this->insertAddress($page, $shipment->getStore());
                /* Add head */
                $this->insertOrder(
                    $page,
                    $shipment,
                    $this->_scopeConfig->isSetFlag(
                        self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $order->getStoreId()
                    )
                );
                /* Add document text and number */
                $this->insertDocumentNumber($page, __('Packing Slip # ') . $shipment->getIncrementId());

                /* Add table */
                $this->_drawHeader($page);
                /* Add body */
                foreach ($shipment->getAllItems() as $item) {
                    if ($item->getOrderItem()->getParentItem()) {
                        continue;
                    }
                    /* Draw item */
                    $this->_drawItem($item, $page, $order);
                    $page = end($pdf->pages);
                }
            }else{
                $products = $this->getVendorData($shipment);
                $_sources = $this->_helper->getSourceInfo($products);
                $this->y = $this->y ? $this->y : 815;
                $top = $this->y;
                $top += 10;
                if($_sources){
                    $page = $this->newPage();
                    $order = $shipment->getOrder();
                    /* Add image */
                    $this->insertLogo($page, $shipment->getStore());
                    /* Add address */
                    $this->insertAddress($page, $shipment->getStore());
                    /* Add head */
                    $this->insertOrder(
                        $page,
                        $shipment,
                        $this->_scopeConfig->isSetFlag(
                            self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $order->getStoreId()
                        )
                    );
                    /* Add document text and number */
                    $this->insertDocumentNumber($page, __('Packing Slip # ') . $shipment->getIncrementId());
                }

                foreach($_sources as $sourceKey => $_source) {
                    /* Add table */
                    $vendorDetails = $this->_productHelper->getVendor($_source->getVendorId());

                    $this->drawText($page,$vendorDetails);

                    $this->_drawHeader($page);

                    /* Add body */
                    foreach ($shipment->getAllItems() as $item) {
                        if ($item->getOrderItem() && $item->getOrderItem()->getParentItem()) {
                            continue;
                        }
                        /* Draw item */
                        $this->_drawItem($item, $page, $order);
                        $page = end($pdf->pages);
                    }
                }
            }

            if ($shipment->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }



    /**
     * draw notice below content
     *
     * @param \Zend_Pdf_Page $page
     */
    protected function drawText(\Zend_Pdf_Page $page,$vendorDetails) {
        $iFontSize = 10;     // font size
        $iColumnWidth = 520; // whole page width
        $iWidthBorder = 400; // half page width
        $soldBy = "SOLD BY: "; // your message
        $vendorName = 'Vendor Name: ' . $vendorDetails->getName();
        $vendorAdress = 'Vendor Address: ' . $vendorDetails->getAddress();
        $vendorPhone = 'Vendor Contact Number: ' . $vendorDetails->getPhone();
        $iXCoordinateText = 30;
        $sEncoding = 'UTF-8';
        $this->y -= 10; // move down on page
        try {
            $oFont = $this->_setFontRegular($page, $iFontSize);

            $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
            $iXCoordinateBorder = 25;
            // draw top border
            $page->drawLine($iXCoordinateBorder, $this->y, $iXCoordinateBorder + $iWidthBorder, $this->y);
            // draw text
            $this->y -= 15;
            $page->drawText($soldBy, $iXCoordinateText, $this->y, $sEncoding);
            $this->y -= 15;
            $page->drawText($vendorName, $iXCoordinateText, $this->y, $sEncoding);
            $this->y -= 15;
            $page->drawText($vendorAdress, $iXCoordinateText, $this->y, $sEncoding);
            $this->y -= 15;
            $page->drawText($vendorPhone, $iXCoordinateText, $this->y, $sEncoding);
            $this->y -= 15;
            // draw bottom border
            $page->drawLine($iXCoordinateBorder, $this->y, $iXCoordinateBorder + $iWidthBorder, $this->y);
            // draw left border
            $page->drawLine($iXCoordinateBorder, $this->y, $iXCoordinateBorder, $this->y + 75);
            // draw right border
            $page->drawLine($iXCoordinateBorder + $iWidthBorder, $this->y, $iXCoordinateBorder + $iWidthBorder, $this->y + 75);
            $this->y -= 15;
        } catch (\Exception $exception) {
            // handle
        }
    }



    public function getVendorData($shipment){
        $products = $shipment->getAllItems();
        foreach ($products as $product){
            $vendorId = $this->getVendorIdByProductId($product->getProductId());
            if($vendorId){
                $sourceStockId = $shipment->getSourceStockId();
                $product->setData('source_stock_id',$sourceStockId);
            }
            $product->setData('vendor_id',$vendorId);
        }
        return $products;
    }

    public function getVendorIdByProductId($id){
        $vendor = $this->_vendorFactory->create();
        $vendorId = $vendor->getResource()->getVendorIdByProductId($id);
        if (empty($vendorId)) {
            return false;
        }
        return $vendorId;
    }

    public function getSourceByProductId($vendorId)
    {
        $conn = $this->_resource->getConnection();

        $table = $conn->getTableName('omnyfy_vendor_location_entity');

        $select = $conn->select()->from(
            $table,
            ['entity_id']
        )
            ->where(
                "vendor_id = ?",
                $vendorId
            )
            ->limit(1)
        ;

        return $conn->fetchOne($select);
    }
}
