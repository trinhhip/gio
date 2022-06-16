<?php

namespace Omnyfy\Mcm\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class PrintInvoice
 * @package Omnyfy\Mcm\Helper
 */
class PrintInvoice extends AbstractHelper
{

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    protected $_vendorData;

    /**
     * @var \Omnyfy\Vendor\Helper\Product
     */
    protected $_productHelper;

    protected $pdfHelper;

    protected $layoutFactory;

    protected $orderRepository;

    protected $mcmHelper;

    protected $_scopeConfig;

    protected $invoiceRepository;

    protected $directoryList;

    protected $file;

    protected $dateTime;
    /**
     * @var \Omnyfy\Mcm\Model\ResourceModel\FeesManagement
     */
    private $feesManagementResource;
    /**
     * @var null
     */
    private $vendorInfo;
    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    private $vendorHelper;
    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    private $logo;
    /**
     * @var \Omnyfy\Vendor\Helper\OrderAttributeHelper
     */
    private $orderAttributeHelper;

    /**
     * @var array
     */
    private $data;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Payment\Helper\Data $paymentData,
        \Omnyfy\Vendor\Helper\Data $vendorData,
        \Omnyfy\Vendor\Helper\Product $productHelper,
        \Omnyfy\Core\Helper\DomPdfInterface $pdfHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagement,
        \Omnyfy\Vendor\Helper\Data $vendorHelper,
        \Omnyfy\Mcm\Helper\OrderAttributeHelper $orderAttributeHelper,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        $this->_vendorData = $vendorData;
        $this->pdfHelper = $pdfHelper;
        $this->layoutFactory = $layoutFactory;
        $this->orderRepository = $orderRepository;
        $this->mcmHelper = $mcmHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_productHelper = $productHelper;
        $this->invoiceRepository = $invoiceRepository;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->dateTime = $dateTime;
        $this->feesManagementResource = $feesManagement;
        $this->vendorInfo = $this->mcmHelper->vendorInfo();
        $this->vendorHelper = $vendorHelper;
        $this->orderAttributeHelper = $orderAttributeHelper;
        parent::__construct($context);
        $this->logo = $logo;
        $this->data = $data;
    }

    public function getInvoicePdf($invoiceId, $vendors = [])
    {
        $invoice = $this->getInvoice($invoiceId);

        if ($invoiceId) {

            if(!is_array($vendors)){
                $vendors = [$vendors];
            }

            $vendors = array_unique($vendors);            

            $htmlContent = $this->generateOrderInvoiceHtml($invoice['order_id'], $invoice, $vendors);

            $this->pdfHelper->newDompdf();
            $this->pdfHelper->setData($htmlContent, 'portrait');

            $date = $this->dateTime->date('Y-m-d_H-i-s');

            $fileName = $invoice->getIncrementId() . '.pdf';

            $filePath = $this->directoryList->getPath(DirectoryList::MEDIA)."/order_invoice/";
            if (! file_exists($filePath)) {
                $this->file->mkdir($filePath);
            }

            $this->saveFile($filePath . $fileName);

            return $filePath . $fileName;
        }
    }

    public function generateOrderInvoiceHtml($orderId, $invoice, $vendors)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layoutFactory->create()->createBlock('Magento\Framework\View\Element\Template');
        $block->setTemplate('Omnyfy_Mcm::order/invoice/order_invoice.phtml');

        $order = $this->orderRepository->get($orderId);

        $logo = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!empty($logo)) {
            $logo = $this->mcmHelper->getMediaUrl() . 'sales/store/logo/' . $logo;
        }

        $total = $this->totalGroup($order, $invoice, $vendors);

        $data = [
            'vendors' => $vendors,
            'order_data' => $this->getOrderData($order, $invoice),
            'invoice_items' => $invoice->getAllItems(),
            'invoice_increment_id' => $invoice['increment_id'],
            'invoice_data' => $this->mcmHelper->getInvoiceFromData(),
            'item_options' => $this->getItemOptions($invoice->getAllItems()),
            'sold_data' => $this->getBillingAddress($orderId),
            'shipping_data' => $this->getShippingAddress($orderId),
            'vendor_info' => $this->vendorInfo,
            'total' => $total,
            'order_attributes' => $this->orderAttributeHelper->getOrderAttributeData($order),
            'logo_url' =>  $logo,
            'taxline' => $this->data['taxline'] ?? null,
            'invoiceBy' => $this->_scopeConfig->getValue('omnyfy_vendor/vendor/invoice_by', ScopeInterface::SCOPE_STORE),
        ];

        $block->setData($data);

        return $block->toHtml();
    }

    public function vendorInfo()
    {
        return $this->mcmHelper->vendorInfo();
    }

    public function getInvoice($invoiceId)
    {
        return $this->invoiceRepository->get($invoiceId);
    }

    public function getSystemConfig($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    protected function saveFile($filePath)
    {
        file_put_contents($filePath, $this->pdfHelper->save());
    }

    protected function getBillingAddress($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        $name = '';
        $city = '';
        $region = '';
        $postCode = '';
        $country = '';
        $telephone = '';
        if ($order->getBillingAddress()) {
            $name = $order->getBillingAddress()->getName();
            $street = $order->getBillingAddress()->getStreet();
            $city = $order->getBillingAddress()->getCity();
            $region = $order->getBillingAddress()->getRegion();
            $postCode = $order->getBillingAddress()->getPostcode();
            $country = $this->mcmHelper->getCountryName($order->getBillingAddress()->getCountryId());
            $telephone = $order->getBillingAddress()->getTelephone();
        } else {
            $street[0] = '';
        }

        $data = [
            'customer_name' => $name,
            'street' => $street[0],
            'city' => $city.', '.$region.', '.$postCode,
            'country' => $country,
            'telephone' => $telephone
        ];

        return $data;
    }

    protected function getShippingAddress($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        $name = '';
        $city = '';
        $region = '';
        $postCode = '';
        $country = '';
        $telephone = '';
        if ($order->getShippingAddress()) {
            $name = $order->getShippingAddress()->getName();
            $street = $order->getShippingAddress()->getStreet();
            $city = $order->getShippingAddress()->getCity();
            $region = $order->getShippingAddress()->getRegion();
            $postCode = $order->getShippingAddress()->getPostcode();
            $country = $this->mcmHelper->getCountryName($order->getShippingAddress()->getCountryId());
            $telephone = $order->getShippingAddress()->getTelephone();
        } else {
            $street[0] = '';
        }

        $data = [
            'customer_name' => $name,
            'street' => $street[0],
            'city' => $city.', '.$region.', '.$postCode,
            'country' => $country,
            'telephone' => $telephone
        ];

        return $data;
    }

    protected function getOrderData($order, $invoice)
    {

        $data = [
            'increment_id' => $order->getIncrementId(),
            'date' => $this->mcmHelper->getDateWithFormat($order->getCreatedAt(), 'd M Y'),
            'mo_name' => $this->mcmHelper->getMoName(),
            'logo_src' => $this->mcmHelper->getLogoSrc(),
            'payment' => $this->getPaymentInfo($order),
            // 'shipment' => $this->getShippingInfo($order, $invoice, $vendorInvoiceTotals),
            // 'vendor' => $this->vendorLocation($order),
            // 'vendor_address' => $this->vendorHelper->getVendorSignUp($this->vendorInfo['vendor_id'])
        ];

        return $data;
    }

    public function getVendorAddress($vendorId){
        return $this->vendorHelper->getVendorSignUp($vendorId);
    }

    protected function getPaymentInfo($order)
    {
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        $result = [];
        if (count($payment) > 1) {
            $result = $payment;
        } else {
            $result['of_payment'] = $payment;
        }

        return $result;
    }

    protected function getShippingInfo($order, $invoice, $vendorInvoiceTotals)
    {
        $shippingData = $this->vendorHelper->getShippingData($order, $invoice);
        $shippingAmount = !empty($vendorInvoiceTotals['shipping_amount']) ? $vendorInvoiceTotals['shipping_amount'] : $invoice->getShippingAmount();
        if (!$invoice->getIsVirtual()) {
            $totalShippingChargesText = "(" . __(
                'Total Shipping Charges'
            ) . " " . $order->formatPriceTxt(
                $shippingAmount
            ) . ")";
            return [
                'shipping_method' => $shippingData['title'],
                'shipping_total' => $totalShippingChargesText
            ];
        }
        return null;
    }

    protected function vendorLocation($order)
    {
        $vendorId = 0;
        if ($this->vendorInfo['vendor_id']) {
            $vendorId = $this->vendorInfo['vendor_id'];
        } else {
            $_locations = $this->_vendorData->getSourceInfo($order->getAllItems());
            foreach ($_locations as $locationKey => $_location) {
                $vendorId = $_location->getVendorId();
                break;
            }
        }
        $vendorDetails = $this->_productHelper->getVendor($vendorId);
        return $vendorDetails->getName() . ' - '.$this->_vendorData->getTaxNumberByVendorId($vendorDetails->getId()).': ' . $vendorDetails->getAbn();
    }

    protected function vendorAddress($order)
    {
        $vendorId = 0;
        if ($this->vendorInfo['vendor_id']) {
            $vendorId = $this->vendorInfo['vendor_id'];
        } else {
            $_locations = $this->_vendorData->getSourceInfo($order->getAllItems());
            foreach ($_locations as $locationKey => $_location) {
                $vendorId = $_location->getVendorId();
                break;
            }
        }
        $vendorDetails = $this->_productHelper->getVendor($vendorId);
        return $vendorDetails->getAddress();
    }

    
    public function totalGroup($order, $invoice, $vendors)
    {
        $vendorInvoiceTotals = [
            'subtotal' => 0,
            'tax_amount' => 0,
            'grand_total' => 0
        ];

        if (count($vendors) > 1) {

            $shipping = $this->getShippingInfo($order, $invoice, $vendorInvoiceTotals);
            $data = [
                'subtotal' => $invoice->getSubtotal(),
                'tax' => $invoice->getTaxAmount(),
                'transaction_fee_incl_tax' => $invoice->getMcmTransactionFeeInclTax(),
                'grand_total' => $invoice->getGrandTotal(),
                'shipping' => $shipping['shipping_total'] ?: null
            ];
        } else {
            $vendorId = current($vendors);
            
            $_t = $this->feesManagementResource->getVendorInvoiceTotals($vendorId, $invoice->getId());
            if ($_t) {
                $vendorInvoiceTotals = $_t;
            }
            $shipping = $this->getShippingInfo($order, $invoice, $vendorInvoiceTotals);
            
            $data = [
                'subtotal' => $vendorInvoiceTotals['subtotal'],
                'tax' => $vendorInvoiceTotals['tax_amount'],
                'grand_total' => $vendorInvoiceTotals['grand_total'],
                'transaction_fee_incl_tax' => $invoice->getMcmTransactionFeeInclTax(),
                'shipping' => $shipping['shipping_total'] ?: null
            ];
        }

        return $data;
    }

    protected function getOrderItems($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        return $order->getAllVisibleItems();
    }

    /**
     * Get item options.
     *
     * @return array
     */
    public function getItemOptions($items)
    {
        $itemOptions = [];
        foreach ($items as $item) {
            if ($item->getOrderItem() && $item->getOrderItem()->getParentItem()) {
                continue;
            }
            $result = [];
            if ($item->getOrderItem()) {
                $options = $item->getOrderItem()->getProductOptions();
                if ($options) {
                    if (isset($options['options'])) {
                        $result[] = $options['options'];
                    }
                    if (isset($options['additional_options'])) {
                        $result[] = $options['additional_options'];
                    }
                    if (isset($options['attributes_info'])) {
                        $result[] = $options['attributes_info'];
                    }
                }
                if (!empty($result)) {
                    $itemOptions[$item->getSku()] = array_merge([], ...$result);
                }
            }
        }
        return $itemOptions;
    }
}
