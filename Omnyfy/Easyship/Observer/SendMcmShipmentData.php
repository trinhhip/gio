<?php
namespace Omnyfy\Easyship\Observer;

class SendMcmShipmentData implements \Magento\Framework\Event\ObserverInterface
{
    protected $easyVendorLocFactory;
    protected $selectedFactory;
    protected $queueHelper;
    protected $scopeConfig;
    protected $sourceCollectionFactory;

    public function __construct(
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Easyship\Model\EasyshipSelectedCourierFactory $selectedFactory,
        \Omnyfy\Core\Helper\Queue $queueHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory
    ){
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->selectedFactory = $selectedFactory;
        $this->queueHelper = $queueHelper;
        $this->scopeConfig = $scopeConfig;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($this->scopeConfig->getValue('carriers/easyship/active', $storeScope)){
            $topic = 'mcm_order_shipment_data';

            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $sourceStockId = $shipment->getSourceStockId();
            $sourceCode = $shipment->getSourceCode();
            $source = $this->sourceCollectionFactory->create()->getSourceAccount($sourceCode);
            // $locationId = $shipment->getLocationId();
//            $locationAcc = $this->easyVendorLocFactory->create()->getLocationAccount($locationId);
            $courier = $this->selectedFactory->create()->getSelectedCourierByQuoteAndSourceStockId($order->getQuoteId(), $sourceStockId);
            $calculateBy = $this->scopeConfig->getValue('omnyfy_vendor/vendor/calculate_shipping_by', $storeScope);

            if($source != null && $courier != null){
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
                    'order_id' => $order->getId(),
                    'vendor_id' => $shipment->getVendorId(),
                    'location_id' => $sourceStockId,
                    'status' => 0,
                    'ship_by_type' => $shipByType,
                    'total_charge' => $totalCharge,
                    'customer_paid' => $courier->getCustomerPaid(),
                    'type' => 'new',
                    'source_stock_id' => $sourceStockId
                ];

                $this->queueHelper->sendMsgToQueue($topic, json_encode($shipment));
            }
        }
    }
}
