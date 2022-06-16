<?php
/**
 * Project: Multi Vendors.
 * User: jing
 * Date: 24/2/18
 * Time: 2:21 AM
 */
namespace Omnyfy\Vendor\Plugin\Sales\Model;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order\ProductOption;
use \Omnyfy\Vendor\Helper\Data as VendorHelper;
use \Omnyfy\Vendor\Helper\Backend as BackendHelper;

class Order extends \Magento\Sales\Model\Order
{
    protected $vendorHelper;

    protected $backendHelper;

    protected $_shipments;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var ProductOption
     */
    private $productOption;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ScopeConfigInterface;
     */
    private $scopeConfig;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var array
     */
    private $regionItems;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    protected $_shipmentCollectionFactory;

    public function __construct(
        VendorHelper $vendorHelper,
        BackendHelper $backendHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productListFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ResolverInterface $localeResolver = null,
        ProductOption $productOption = null,
        OrderItemRepositoryInterface $itemRepository = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        ScopeConfigInterface $scopeConfig = null,
        RegionFactory $regionFactory = null
    ) {
        $this->vendorHelper = $vendorHelper;
        $this->backendHelper = $backendHelper;
        $this->localeResolver = $localeResolver ?: ObjectManager::getInstance()->get(ResolverInterface::class);
        $this->productOption = $productOption ?: ObjectManager::getInstance()->get(ProductOption::class);
        $this->itemRepository = $itemRepository ?: ObjectManager::getInstance()
            ->get(OrderItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->regionFactory = $regionFactory ?: ObjectManager::getInstance()->get(RegionFactory::class);
        $this->regionItems = [];
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $timezone, $storeManager, $orderConfig, $productRepository, $orderItemCollectionFactory, $productVisibility, $invoiceManagement, $currencyFactory, $eavConfig, $orderHistoryFactory, $addressCollectionFactory, $paymentCollectionFactory, $historyCollectionFactory, $invoiceCollectionFactory, $shipmentCollectionFactory, $memoCollectionFactory, $trackCollectionFactory, $salesOrderCollectionFactory, $priceCurrency, $productListFactory, $resource, $resourceCollection, $data, $localeResolver, $productOption, $itemRepository, $searchCriteriaBuilder, $scopeConfig, $regionFactory);
    }

    public function setShippingMethod($shippingMethod)
    {
        if (is_array($shippingMethod)) {
            $shippingMethod = $this->vendorHelper->shippingMethodArrayToString($shippingMethod);
        }
        return $this->setData('shipping_method', $shippingMethod);
    }

    public function getShipmentsCollection()
    {
        if (empty($this->_shipments)) {
            if ($this->getId()) {
                if($this->getVendorId()) {
                    $this->_shipments = $this->_shipmentCollectionFactory->create()->setOrderFilter($this)->addFieldToFilter('vendor_id', $this->getVendorId())->load();
                } else {
                    $this->_shipments = $this->_shipmentCollectionFactory->create()->setOrderFilter($this)->load();
                }
            } else {
                return false;
            }
        }
        return $this->_shipments;
    }

    public function getCreditmemosCollection()
    {
        if (empty($this->_creditmemos)) {
            if ($this->getId()) {
                if($this->getVendorId()) {
                    $this->_creditmemos = $this->_memoCollectionFactory->create()->setOrderFilter($this)->addFieldToFilter('vendor_id', $this->getVendorId())->load();

                } else {
                    $this->_creditmemos = $this->_memoCollectionFactory->create()->setOrderFilter($this)->load();
                }
            } else {
                return false;
            }
        }
        return $this->_creditmemos;
    }

    public function getInvoiceCollection()
    {
        if ($this->_invoices === null) {
            if($this->getVendorId()) {
                $this->_invoices = $this->_invoiceCollectionFactory->create()->setOrderFilter($this)->addFieldToFilter('vendor_id', $this->getVendorId());

            } else {
                $this->_invoices = $this->_invoiceCollectionFactory->create()->setOrderFilter($this);
            }

            if ($this->getId()) {
                foreach ($this->_invoices as $invoice) {
                    $invoice->setOrder($this);
                }
            }
        }
        return $this->_invoices;
    }

    public function getStatusHistoryCollection()
    {
        if($this->getVendorId()) {
            $collection = $this->_historyCollectionFactory->create()->setOrderFilter($this)
                ->addFieldToFilter('vendor_id',['in' => [0, $this->getVendorId()]])
                ->setOrder('created_at', 'desc')
                ->setOrder('entity_id', 'desc');
        } else {
            $collection = $this->_historyCollectionFactory->create()->setOrderFilter($this)
                ->setOrder('created_at', 'desc')
                ->setOrder('entity_id', 'desc');
        }

        if ($this->getId()) {
            foreach ($collection as $status) {
                $status->setOrder($this);
            }
        }
        return $collection;
    }

    public function getVendorId(){
        return $this->backendHelper->getBackendVendorId();
    }
}
