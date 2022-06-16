<?php

namespace Omnyfy\Mcm\Controller\Adminhtml\PayoutHistory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Omnyfy\Mcm\Model\VendorPayoutInvoiceFactory;
use Omnyfy\Mcm\Model\VendorPayoutInvoice\VendorPayoutInvoiceOrderFactory;
use Omnyfy\Mcm\Model\VendorOrderFactory;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Plugin\Vendor\Model\Vendor;
use Omnyfy\RebateCore\Model\Repository\TransactionRebateRepository;
use Omnyfy\Mcm\Api\VendorPayoutInterface;

class VendorInvoice extends \Omnyfy\Mcm\Controller\Adminhtml\AbstractAction
{

    protected $resourceKey = 'Omnyfy_Mcm::payout_history';
    protected $adminTitle = 'Payout History';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     * @var VendorPayoutInterface
     */
    protected $vendorPayoutInterface;

    protected $_abstractPdf;

    protected $vendorPayoutInvoiceFactory;

    protected $vendorPayoutInvoiceOrderFactory;

    protected $_mcmHelper;

    protected $vendorOrderFactory;

    protected $transactionRebateRepository;

    protected $orderRepository;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var \Omnyfy\Vendor\Model\Config
     */
    public $_vendorConfigHelper;

    protected $payoutHistoryCollection;

    protected $layoutFactory;

    protected $pdfHelper;

    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    protected $_vendorData;

    /**
     * @var \Omnyfy\Mcm\Model\FeesChargesFactory
     */
    protected $feesChargesFactory;

    protected $vendorFactory;

    private $storeManager;

    /**
     * Y coordinate
     *
     * @var int
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Omnyfy\Mcm\Model\VendorPayoutInvoice\Pdf\AbstractPdf $abstractPdf,
        VendorPayoutInvoiceFactory $vendorPayoutInvoiceFactory,
        VendorPayoutInvoiceOrderFactory $vendorPayoutInvoiceOrderFactory,
        VendorOrderFactory $vendorOrderFactory,
        HelperData $helper,
        TransactionRebateRepository $transactionRebateRepository,
        VendorPayoutInterface $vendorPayoutInterface,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omnyfy\Vendor\Model\Config $vendorConfigHelper,
        \Omnyfy\Mcm\Model\ResourceModel\VendorPayoutHistory\CollectionFactory $payoutHistoryCollection,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Omnyfy\Core\Helper\DomPdfInterface $pdfHelper,
        \Omnyfy\Vendor\Helper\Data $vendorData,
        \Omnyfy\Mcm\Model\FeesChargesFactory $feesChargesFactory,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_abstractPdf = $abstractPdf;
        $this->vendorPayoutInvoiceFactory = $vendorPayoutInvoiceFactory;
        $this->vendorPayoutInvoiceOrderFactory = $vendorPayoutInvoiceOrderFactory;
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->_mcmHelper = $helper;
        $this->transactionRebateRepository = $transactionRebateRepository;
        $this->vendorPayoutInterface = $vendorPayoutInterface;
        $this->orderRepository = $orderRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_vendorConfigHelper = $vendorConfigHelper;
        $this->payoutHistoryCollection = $payoutHistoryCollection;
        $this->layoutFactory = $layoutFactory;
        $this->pdfHelper = $pdfHelper;
        $this->_vendorData = $vendorData;
        $this->feesChargesFactory = $feesChargesFactory;
        $this->vendorFactory = $vendorFactory;
        $this->storeManager = $storeManager;

        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
    }

    public function execute()
    {

        $error = 1;
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoiceData = $this->vendorPayoutInvoiceFactory->create()->load($invoiceId);
            if (!empty($invoiceData->getData())) {
                $error = 0;
                try {
                    $htmlContent = $this->generateInvoiceHtml($invoiceId, $invoiceData);
                    $this->pdfHelper->setData($htmlContent);
                    $this->pdfHelper->render();
                } catch (\Exception $exception) {
                    $error = 1;
                    $this->messageManager->addErrorMessage(__($exception->getMessage()));
                    $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                    $resultRedirect->setPath('omnyfy_mcm/payouthistory/index');
                    return $resultRedirect;
                }
            }
        }
        if ($error) {
            $this->messageManager->addErrorMessage(__("This Invoice doesn't exist."));
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('omnyfy_mcm/payouthistory/index');
            return $resultRedirect;
        }
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function generateInvoiceHtml($invoiceId, $invoiceData)
    {
        $payoutOrderData = $this->vendorPayoutInvoiceOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $invoiceData->getVendorId())
            ->addFieldToFilter('invoice_id', $invoiceId)
            ->getFirstItem();

        $vendor = $this->vendorFactory->create()->load($invoiceData->getVendorId());

        $template = (int)$vendor->getPayoutBasisType() == 1 ? 'pdfInvoice_wholesale.phtml' : 'pdfInvoice.phtml';

        $vendorOrderData = $this->vendorOrderFactory->create();

        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layoutFactory->create()->createBlock('Magento\Framework\View\Element\Template');
        $block->setTemplate('Omnyfy_Mcm::payout_history/' . $template);


        $logo = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!empty($logo)) {
            $logo = $this->getMediaUrl() . 'sales/store/logo/' . $logo;
        }

        $vendorPayoutBasisType = (int)$vendor->getPayoutBasisType();

        $costByOrder = $this->getTotalCostByOrder($invoiceData, $vendorPayoutBasisType);

        $totalShippingCostCalculated = $this->getTotalShippingCost($invoiceData);

        $data = [
            'invoice_data' => $invoiceData,
            'total_shipping_cost_calculated' => $totalShippingCostCalculated,
            'logo' => $logo,
            'feecharge' => $this->feesChargesFactory->create()->load($invoiceData->getVendorId(), 'vendor_id'),
            'total_cost_by_order' => $costByOrder,
            'invoice_total' => array_reduce($costByOrder, function ($sum, $e) {
                $sum += $e;
                return $sum;
            }),
        ];
        $block->setData($data);

        return $block->toHtml();
    }

    private function getTotalCostByOrder($invoiceData, $vendorPayoutBasisType)
    {
        $return = [];
        foreach ($invoiceData->getAllInvoiceOrders() as $key => $order) {
            if ($vendorPayoutBasisType) {
                $return[$order->getId()] = $this->vendorPayoutInterface->getPayoutTotalWholesaleVendor($order->getOrderId(), $invoiceData->getVendorId(), null, true);
            } else {
                $return[$order->getId()] = $this->vendorPayoutInterface->getPayoutTotalWholesaleVendor($order->getOrderId(), $invoiceData->getVendorId());
            }
        }
        return $return;
    }

    public function getMoName()
    {
        $moName = $this->_scopeConfig->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $moName;
    }

    public function getInvoiceFrom($invoiceData)
    {
        $storeCity = $this->_scopeConfig->getValue(
            'general/store_information/city',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $storeRegionName = $this->_abstractPdf->getRegionName($this->_scopeConfig->getValue(
            'general/store_information/region_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        $storePostcode = $this->_scopeConfig->getValue(
            'general/store_information/postcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $storeCountryName = $this->_abstractPdf->getCountryName($this->_scopeConfig->getValue(
            'general/store_information/country_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
        $storePhone = $this->_scopeConfig->getValue(
            'general/store_information/phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $storeAddressLine1 = $this->_scopeConfig->getValue(
            'general/store_information/street_line1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $storeAddressLine2 = $this->_scopeConfig->getValue(
            'general/store_information/street_line2',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $storeAddress = [
            $this->_scopeConfig->getValue(
                'general/store_information/street_line1',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            $this->_scopeConfig->getValue(
                'general/store_information/street_line2',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            ),
            ($storeCity ? $storeCity . ', ' : '') . ($storeRegionName ? $storeRegionName . ', ' : '') . $storePostcode,
            $storeCountryName,
            $storePhone ? 'T: ' . $storePhone : ''
        ];

        $invoiceFrom = [
            'city' => $storeCity,
            'region_name' => $storeRegionName,
            'post_code' => $storePostcode,
            'country_name' => $storeCountryName,
            'phone' => $storePhone,
            'address_line1' => $storeAddressLine1,
            'address_line2' => $storeAddressLine2,
            'address' => $storeAddress,
            'tax_number' => $this->_vendorConfigHelper->getMoAbn()
        ];

        return $invoiceFrom;
    }

    public function getDateFormat($invoiceData)
    {
        return $this->_abstractPdf->getDateWithFormat($invoiceData->getCreatedAt(), 'd M Y');
    }

    public function getVendorTableData($order)
    {
        $payoutAmount = $this->vendorPayoutInterface->getPayoutAmount($order->getVendorId(), $order->getOrderId()) ?? 0;
        $rebate = $this->transactionRebateRepository->getPerOrderSettlementTransactions($order->getVendorId(), $order->getOrderId()) ?? 0;

        $data = [
            'increment_id' => $order->getOrderIncrementId(),
            'order_total_incl_tax' => $this->_abstractPdf->currency($order->getOrderTotalInclTax()),
            'shipping_total_order' => $this->_abstractPdf->currency($order->getShippingTotalForOrder()),
            'order_total_tax' => $this->_abstractPdf->currency($order->getOrderTotalTax()),
            'fees_total_incl_tax' => $this->_abstractPdf->currency($order->getFeesTotalInclTax()),
            'fees_total_tax' => $this->_abstractPdf->currency($order->getFeesTotalTax()),
            'rebate' => $this->_abstractPdf->currency($rebate),
            'payout_amount' => $this->_abstractPdf->currency($payoutAmount)
        ];

        return $data;
    }

    public function getPayoutType($invoiceData)
    {
        $payoutHistory = $this->payoutHistoryCollection->create();
        $payoutHistory->getSelect()->join(
            ['type' => $payoutHistory->getTable('omnyfy_mcm_payout_type')],
            'type.id = main_table.payout_type_id',
            ['payout_type']
        )->join(
            ['invoice' => $payoutHistory->getTable('omnyfy_mcm_vendor_payout_invoice')],
            'main_table.payout_ref = invoice.payout_ref and main_table.vendor_id = invoice.vendor_id',
            ['increment_id']
        )->where('invoice.increment_id = ?', $invoiceData->getIncrementId());
        $payoutType = $payoutHistory->getFirstItem()->getPayoutType();

        return $payoutType;
    }

    /**
     * Set font as regular
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('app/code/Omnyfy/Mcm/view/adminhtml/web/font/Arial.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('app/code/Omnyfy/Mcm/view/adminhtml/web/font/ArialBold.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('app/code/Omnyfy/Mcm/view/adminhtml/web/font/ArialItalic.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    public function currency($value)
    {
        return $this->_mcmHelper->formatToBaseCurrency($value);
    }

    public function getTotalShippingCost($invoiceData)
    {
        $shippingTotal = 0;
        foreach ($invoiceData->getAllInvoiceOrders() as $key => $order) {
            $shippingTotal += $order->getShippingTotalForOrder();
        }

        return $shippingTotal;
    }

    public function totalEarning($invoiceData)
    {
        $total = 0;
        foreach ($invoiceData->getAllInvoiceOrders() as $key => $order) {
            $payoutAmount = $this->vendorPayoutInterface->getPayoutAmount($order->getVendorId(), $order->getOrderId()) ?? 0;
            $total += $payoutAmount;
        }
        return $total;
    }

    public function getOrderItems($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        return $order->getAllVisibleItems();
    }
}
