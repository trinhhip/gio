<?php

namespace Omnyfy\Vendor\Block\Adminhtml\Items\Column;


use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class ShippingTotal extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{

    protected $vendorRepository;
    
    protected $feesManagementResource;

    protected $_helper;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
		PriceCurrencyInterface $priceCurrencyInterface,
        StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = [])
    {
		$this->priceCurrency = $priceCurrencyInterface;
        $this->_storeManager = $storeManager;
		$this->orderRepository = $orderRepository;
        $this->_resource = $resource;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }

    public function getShipping($item) {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $quoteId = $order->getData('quote_id');
        $vendorId = $item->getVendorId();
        $sourceStockId = $item->getData('source_stock_id');
        $shippingTotal = $this->getShippingRate($quoteId,$vendorId,$sourceStockId);
        return $this->formatToBaseCurrency($shippingTotal);
    }

    public function getShippingRate($quoteId,$vendorId,$sourceStockId) {
        $query = 'SELECT amount FROM omnyfy_vendor_quote_shipping WHERE quote_id = '.$quoteId.' && vendor_id = '.$vendorId.' && source_stock_id ='.$sourceStockId;
        $result = $this->_resource->getConnection()->fetchOne($query);
        if($result) {
            return $result;
        }
        return ;
    }

    public function formatToBaseCurrency($amount = 0) {
        $baseCurrency = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
        return $this->priceCurrency->format($amount, false, null, null, $baseCurrency);
    }
}
