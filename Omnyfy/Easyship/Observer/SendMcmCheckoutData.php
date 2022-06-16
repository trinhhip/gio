<?php
namespace Omnyfy\Easyship\Observer;

class SendMcmCheckoutData implements \Magento\Framework\Event\ObserverInterface
{
    protected $scopeConfig;
    protected $orderInterface;
    protected $easyVendorLocFactory;
    protected $selectedFactory;
    protected $queueHelper;
    protected $sourceCollectionFactory;
    protected $vendorSourceStockResource;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Easyship\Model\EasyshipSelectedCourierFactory $selectedFactory,
        \Omnyfy\Core\Helper\Queue $queueHelper,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vendorSourceStockResource
    ){
        $this->scopeConfig = $scopeConfig;
        $this->orderInterface = $orderInterface;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->selectedFactory = $selectedFactory;
        $this->queueHelper = $queueHelper;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vendorSourceStockResource = $vendorSourceStockResource;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) 
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue('carriers/easyship/active', $storeScope)){
            $topic = 'mcm_order_shipment_data';

            $orderIds = $observer->getEvent()->getOrderIds();
            $order = $this->orderInterface->load($orderIds[0]);
            $quoteId = $order->getQuoteId();

            $arrSourceStockId = [];
            $calculateBy = $this->scopeConfig->getValue('omnyfy_vendor/vendor/calculate_shipping_by', $storeScope);

            foreach ($order->getAllItems() as $item) {
                $sourceStockId = $item->getSourceStockId();

                if ($calculateBy == 'overall_cart') {
                    $sourceStockId = $this->scopeConfig->getValue('omnyfy_vendor/vendor/overall_pickup_location', $storeScope);
                }

                if (!in_array($sourceStockId, $arrSourceStockId)) {
                    $sourceCode = $this->vendorSourceStockResource->getSourceCodeById($sourceStockId);
                    $source = $this->sourceCollectionFactory->create()->getSourceAccount($sourceCode);
                    // $locationAcc = $this->easyVendorLocFactory->create()->getLocationAccount($locationId);
                    $courier = $this->selectedFactory->create()->getSelectedCourierByQuoteAndSourceStockId($quoteId, $sourceStockId);

                    if ($source != null && $courier != null) {
                        $shipByType = 1;
                        if ($calculateBy != 'overall_cart' && $source->getCreatedByMo() == 0) {
                            // 1 = marketplace owner
                            // 2 = vendor
                            $shipByType = 2;
                        }

                        $totalCharge = 0;
                        if ($source->getUseLiveRate() == 1) {
                            $totalCharge = $courier->getTotalCharge(); //live rate
                        }

                        $shipment = [
                            'order_id' => $orderIds[0],
                            'vendor_id' => $item->getVendorId(),
                            'location_id' => $sourceStockId,
                            'status' => 0,
                            'ship_by_type' => $shipByType,
                            'total_charge' => $totalCharge,
                            'customer_paid' => $courier->getCustomerPaid(),
                            'type' => 'new',
                            'source_stock_id' => $sourceStockId
                        ];

                        $this->queueHelper->sendMsgToQueue($topic, json_encode($shipment));
                        array_push($arrSourceStockId, $sourceStockId);
                    }
                }
            }
        }

    }
}
