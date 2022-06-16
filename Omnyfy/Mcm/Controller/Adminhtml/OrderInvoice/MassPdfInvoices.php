<?php

namespace Omnyfy\Mcm\Controller\Adminhtml\OrderInvoice;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;

class MassPdfInvoices extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\Pdfinvoices
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

    protected $directoryList;

    protected $file;

    protected $deleteDirectory;

    /**
     * @var array
     */
    private $data;

    public function __construct(
        Context $context,
        Filter $filter,
        DateTime $dateTime,
        FileFactory $fileFactory,
        Invoice $pdfInvoice,
        CollectionFactory $collectionFactory,
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
        \Omnyfy\Mcm\Model\DeleteDirectory $deleteDirectory,
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
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->deleteDirectory = $deleteDirectory;
        $this->data = $data;
        parent::__construct($context, $filter, $dateTime, $fileFactory, $pdfInvoice, $collectionFactory);
    }

    public function massAction(AbstractCollection $collection)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $invoices = $collection->getData();

        if (!empty($invoices)) {
            $zipPath = 'invoice'.time();
            try {
                foreach ($invoices as $invoice) {
                    $orderId = $invoice['order_id'];
                    $htmlContent = $this->generateOrderInvoiceHtml($orderId, $invoice);
                    $this->pdfHelper->newDompdf();
                    $this->pdfHelper->setData($htmlContent);

                    $fileName = 'invoice' . $invoice['increment_id'] . '.pdf';
                    $filePath = $this->directoryList->getPath(DirectoryList::MEDIA)."/order_invoice/".$zipPath;
                    if (! file_exists($filePath)) {
                        $this->file->mkdir($filePath);
                    }

                    $this->saveFile($filePath .'/'. $fileName);
                }
                $this->downloadFile($zipPath);
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(__($exception->getMessage()));
            }
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('sales/invoice/index');
            return $resultRedirect;
        }
    }

    public function generateOrderInvoiceHtml($orderId, $invoice)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layoutFactory->create()->createBlock('Magento\Framework\View\Element\Template');
        $block->setTemplate('Omnyfy_Mcm::order/invoice/order_invoice.phtml');

        $logo = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!empty($logo)) {
            $logo = $this->mcmHelper->getMediaUrl() . 'sales/store/logo/' . $logo;
        }

        $data = [
            'order_data' => $this->getOrderData($orderId),
            'order_items' => $this->getOrderItems($orderId),
            'invoice_increment_id' => $invoice['increment_id'],
            'invoice_data' => $this->mcmHelper->getInvoiceFromData(),
            'sold_data' => $this->getBillingAddress($orderId),
            'shipping_data' => $this->getShippingAddress($orderId),
            'vendor_info' => $this->vendorInfo(),
            'total' => $this->totalGroup($orderId, $invoice),
            'logo_url' => $logo,
            'taxline' => $this->data['taxline'] ?? null,
        ];

        $block->setData($data);

        return $block->toHtml();
    }

    protected function saveFile($filePath)
    {
        file_put_contents($filePath, $this->pdfHelper->save());
    }

    protected function downloadFile($zipPath)
    {
        try {
            $dir = $this->directoryList->getPath(DirectoryList::ROOT);
            $rootPath = $dir.'/pub/media/order_invoice/'.$zipPath;
            chdir($rootPath);

            $zip = new \ZipArchive();
            $zip->open($zipPath.'.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                // Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();

            //Download
            $this->fileFactory->create(
                $zipPath.'.zip',
                [
                    'type' => 'filename',
                    'value' => 'media/order_invoice/'.$zipPath.'/'.$zipPath.'.zip',
                    'rm' => 1
                ],
                DirectoryList::PUB,
                'application/zip'
            );

            //Delete file
            if ($rootPath) {
                $this->deleteDirectory->deleteDirectory($rootPath);
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("ZipArchive class not found"));
        }
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

    protected function getOrderData($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        $data = [
            'increment_id' => $order->getIncrementId(),
            'date' => $this->mcmHelper->getDateWithFormat($order->getCreatedAt(), 'd M Y'),
            'mo_name' => $this->mcmHelper->getMoName(),
            'logo_src' => $this->mcmHelper->getLogoSrc(),
            'payment' => $this->getPaymentInfo($order),
            'shipment' => $this->getShippingInfo($order),
            'vendor' => $this->vendorLocation($order)
        ];

        return $data;
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

    protected function getShippingInfo($order)
    {
        if (!$order->getIsVirtual()) {
            $shippingMethod = $order->getShippingDescription();
            $totalShippingChargesText = "(" . __(
                'Total Shipping Charges'
            ) . " " . $order->formatPriceTxt(
                $order->getShippingAmount()
            ) . ")";
            return [
                'shipping_method' => $shippingMethod,
                'shipping_total' => $totalShippingChargesText
            ];
        }
        return null;
    }

    protected function vendorLocation($order)
    {
        $_locations = $this->_vendorData->getLocationsInfo($order->getAllItems());
        foreach ($_locations as $locationKey => $_location) {
            $vendorDetails = $this->_productHelper->getVendor($_location->getVendorId());
            return $vendorDetails->getName() . ' - '.$this->_vendorData->getTaxNumberByVendorId($vendorDetails->getId()).': ' . $vendorDetails->getAbn();
        }
    }

    public function vendorInfo()
    {
        return $this->mcmHelper->vendorInfo();
    }

    protected function totalGroup($orderId, $invoice)
    {
        $vendorInfo = $this->vendorInfo() ?? null;
        $orderItems = $this->getOrderItems($orderId);
        $transactionFee = $invoice['mcm_transaction_fee_incl_tax'];

        $subtotal = 0;
        $tax = 0;
        foreach ($orderItems as $orderItem) {
            if (empty($vendorInfo)) {
                $subtotal += $orderItem->getRowTotal();
                $tax += $orderItem->getTaxAmount();
            } else {
                if ($vendorInfo['vendor_id'] == $orderItem->getVendorId()) {
                    $subtotal += $orderItem->getRowTotal();
                    $tax += $orderItem->getTaxAmount();
                }
            }
        }

        $data = [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'transaction_fee_incl_tax' => $transactionFee,
            'grand_total' => $subtotal + $tax + $transactionFee
        ];

        return $data;
    }

    protected function getOrderItems($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        return $order->getAllVisibleItems();
    }
}
