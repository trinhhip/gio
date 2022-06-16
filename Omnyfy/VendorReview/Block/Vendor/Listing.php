<?php

namespace Omnyfy\VendorReview\Block\Vendor;

use Magento\Customer\Model\Context;

class Listing extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_scopeConfig;

    /**
     * @var \Omnyfy\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * Review resource model
     *
     * @var \Omnyfy\VendorReview\Model\ResourceModel\Review\Vendor\CollectionFactory
     */
    protected $_vendorReviewCollectionFactory;

    /**
     * Review resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory
     */
    protected $_productReviewCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    protected $_productRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Omnyfy\VendorReview\Model\ResourceModel\Review\Vendor\CollectionFactory $vendorReviewCollectionFactory,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productReviewCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory = null,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_scopeConfig = $scopeConfig;
        $this->vendorFactory = $vendorFactory;
        $this->currentCustomer = $currentCustomer;
        $this->_vendorReviewCollectionFactory = $vendorReviewCollectionFactory;
        $this->_productReviewCollectionFactory = $productReviewCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory::class);
        $this->_productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getOrderStatus() {
        return $this->getOrder()->getStatus();
    }

    public function getOrderProducts() {

        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->setOrderFilter($this->getOrder());

        return $itemCollection->getItems();
    }

    public function getListProducts() {
        $products = [];
        $invoices = $this->getOrder()->getInvoiceCollection();
        foreach ($invoices as $invoice) {
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                if (!$item->getOrderItem()->getParentItem()) {
                    $products[] = $item;
                }
            }
        }
        return $products;
    }

    

    public function getVendorByProductId($productId)
    {
        if ($this->_scopeConfig->isSetFlag(\Omnyfy\Vendor\Model\Config::XML_PATH_VENDOR_SHARE_PRODS)) {
            return false;
        }

        $vendor = $this->vendorFactory->create();

        $vendorId = $vendor->getResource()->getVendorIdByProductId($productId);
        if (empty($vendorId)) {
            return false;
        }
        $vendor->load($vendorId);
        if ($vendor->getId() == $vendorId) {
            return $vendor;
        }
        return false;
    }

    public function getVendorById($vendorId)
    {
        if (!$vendorId) {
            return false;
        }
        $vendor = $this->vendorFactory->create()->load($vendorId);
        return $vendor;
    }

    public function getVendorListOnly() {
        $products = $this->getOrderProducts();
        $result = [];

        foreach ($products as $product) {
            $productId = $product->getProductId();

            $vendor = $this->getVendorByProductId($productId);

            if($vendor) {
                $vendorId = $vendor->getId();

                if (!array_key_exists($vendorId,$result)) {
                    $result[$vendorId] = $vendor;
                }

            }
        }

        return $result;
    }

    public function getVendorListWithProduct() {
        $products = $this->getListProducts();

        $result['vendors'] = [];
        $result['products'] = [];

        foreach ($products as $product) {
            $productId = $product->getProductId();

            $vendor = $this->getVendorByProductId($productId);

            if($vendor) {
                $vendorId = $vendor->getId();

                if (!array_key_exists($vendorId,$result)) {
                    $result['vendors'][$vendorId] = $vendor;
                }

            } else {
                $vendorId = 'no-vendor';
            }

            $result['products'][$vendorId][] = $product;
        }

        return $result;
    }

     public function getReviewByVendorId($vendorId) {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return null;
        }
        
        $collection = $this->_vendorReviewCollectionFactory->create();
        $collection
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addCustomerFilter($customerId)
            ->addEntityFilter($vendorId)
            ->setDateOrder()
            ->setPageSize(1)
            ->setCurPage(1);

        if(!count($collection)) {
            return null;
        }

        $review = '';

        foreach ($collection as $value) {
            $review = $value;
            break;
        }
       
        return $review;
    }

    public function getReviewByProductId($productId) {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return null;
        }
        $collection = $this->_productReviewCollectionFactory->create();
        $collection
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addCustomerFilter($customerId)
            ->addEntityFilter($productId)
            ->setDateOrder()
            ->setPageSize(1)
            ->setCurPage(1);
        
        if(!count($collection)) {
            return null;
        }

        $review = '';

        foreach ($collection as $value) {
            $review = $value;
            break;
        }
       
        return $review;
    }

    public function getReviewVendorLink()
    {
        return $this->getUrl('vendorreview/customer/view/');
    }

    public function getReviewProductLink()
    {
        return $this->getUrl('review/customer/view/');
    }

}
