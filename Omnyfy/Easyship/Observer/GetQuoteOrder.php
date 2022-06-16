<?php
namespace Omnyfy\Easyship\Observer;

class GetQuoteOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory
     */
    protected $_easyshipVendorLocFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Omnyfy\Easyship\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $_response;

    protected $sourceCollectionFactory;

    protected $vSourceStockResource;

    /**
    * @param \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyshipVendorLocFactory
    * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    * @param \Omnyfy\Easyship\Helper\Data $dataHelper
    * @param \Magento\Sales\Model\OrderFactory $orderFactory
    * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
    * @param \Magento\Framework\App\Response\Http $response
    */
    public function __construct(
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyshipVendorLocFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omnyfy\Easyship\Helper\Data $dataHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\App\Response\Http $response,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource
    ){
        $this->_easyshipVendorLocFactory = $easyshipVendorLocFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_dataHelper = $dataHelper;
        $this->_orderFactory = $orderFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->_response = $response;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vSourceStockResource = $vSourceStockResource;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->_scopeConfig->getValue('carriers/easyship/active', $storeScope)){
            $orderIds = $observer->getEvent()->getOrderIds();
            $order = $this->_orderFactory->create()->load($orderIds[0]);
            $quote = $this->_quoteFactory->create()->load($order->getQuoteId());
            $items = $order->getAllVisibleItems();
            $quoteId = $quote->getId();
            $shippingMethods = [];
            $shippingAddress = $quote->getShippingAddress();
            $rates = $shippingAddress->getAllShippingRates();
            if(empty($rates)){
                return;
            }
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if(!$shippingMethod || empty(json_decode($quote->getShippingAddress()->getShippingMethod()))){
                return;
            }
            // identify shipping method selected and get location ID
            if(!$order->getIsVirtual()) {
                foreach ($rates as $rate) {
                    foreach (json_decode($quote->getShippingAddress()->getShippingMethod()) as $shippingCode) {
                        if ($shippingCode == $rate->getCode()) {
                            $shippingMethods[$rate->getSourceStockId()]['carrier_title'] = $rate->getCarrierTitle();
                            $shippingMethods[$rate->getSourceStockId()]['carrier_code'] = $rate->getCarrier();
                            $shippingMethods[$rate->getSourceStockId()]['vendor_id'] = $rate->getVendorId();
                            $shippingMethods[$rate->getSourceStockId()]['courier_id'] = $rate->getMethodDescription();
                            $shippingMethods[$rate->getSourceStockId()]['price'] = $rate->getPrice();
                        }
                    }
                }

                foreach ($shippingMethods as $sourceStockId => $shippingMethod) {
                    if ($shippingMethod['carrier_code'] == 'easyship') {
                        $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
                        $source = $this->sourceCollectionFactory->create()->getAccountRateOptionBySource($sourceCode);
                        // $account = $this->_easyshipVendorLocFactory->create()->getAccountRateOptionByLocation($locationId);
                        if ($source != null) {
                            $ship_by_marketplace = ($source->getCreatedByMo()) ? true : false;
                            $calculateShippingBy = $this->_scopeConfig->getValue('omnyfy_vendor/vendor/calculate_shipping_by', $storeScope);

                            if ($source->getUseLiveRate()) { // live rate
                                $itemDetail = [];
                                foreach ($items as $item) {
                                    if ($item->getQtyToShip() <= 0 || $item->getIsVirtual()) {
                                        continue;
                                    }
                                    if ($calculateShippingBy == 'overall_cart' || $sourceStockId == $item->getSourceStockId()) {
                                        $itemDetail[] = [
                                            "quote_item_id" => $item->getQuoteItemId(),
                                            "order_item_id" => $item->getItemId()
                                        ];
                                    }
                                }

                                $dataSelectedCourier = [
                                    "quote_id" => $quoteId,
                                    "vendor_id" => $shippingMethod['vendor_id'],
                                    "vendor_location_id" => $sourceStockId,
                                    "courier_id" => $shippingMethod['courier_id'],
                                    "courier_name" => $shippingMethod['carrier_title'],
                                    "shipping_rate_option_id" => NULL,
                                    "total_charge" => $shippingMethod['price'],
                                    "order_id" => $order->getId(),
                                    "ship_by_marketplace" => $ship_by_marketplace,
                                    "easyship_account_id" => $source->getEasyshipAccountId(),
                                    "source_stock_id" => $sourceStockId,
                                    "items_detail" => $itemDetail
                                ];
                                $this->_dataHelper->saveSelectedCourier($dataSelectedCourier);

                            } else if (!$source->getUseLiveRate()) { // fixed rate
                                $itemDetail = [];
                                foreach ($items as $item) {
                                    if ($item->getQtyToShip() <= 0 || $item->getIsVirtual()) {
                                        continue;
                                    }
                                    if ($calculateShippingBy == 'overall_cart' || $sourceStockId == $item->getSourceStockId()) {
                                        $itemDetail[] = [
                                            "quote_item_id" => $item->getQuoteItemId(),
                                            "order_item_id" => $item->getItemId()
                                        ];
                                    }
                                }

                                $dataSelectedCourier = [
                                    "quote_id" => $quoteId,
                                    "vendor_id" => $shippingMethod['vendor_id'],
                                    "vendor_location_id" => $sourceStockId,
                                    "courier_id" => '',
                                    "courier_name" => '',
                                    "shipping_rate_option_id" => $source->getShippingRateOptionId(),
                                    "total_charge" => $source->getPriceRateOption(),
                                    "order_id" => $order->getId(),
                                    "ship_by_marketplace" => $ship_by_marketplace,
                                    "easyship_account_id" => $source->getEasyshipAccountId(),
                                    "items_detail" => $itemDetail,
                                    "source_stock_id" => $sourceStockId
                                ];
                                $this->_dataHelper->saveSelectedCourier($dataSelectedCourier);
                            }
                        }
                    }
                }
            }
            return $this;
        }
    }

    /**
     * @param $msg
     * @param null $status
     */
    protected function error($msg, $status = null)
    {
        if ($status && $status > 0)
            $responseStatus = $status;
        else
            $responseStatus = 202;

        $this->_response
            ->setStatusCode($responseStatus)
            ->setContent($msg);

        $this->log("$responseStatus $msg");
    }

    /**
     * @param $msg
     */
    protected function log($msg)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/get-quote-order.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($msg);
    }
}
