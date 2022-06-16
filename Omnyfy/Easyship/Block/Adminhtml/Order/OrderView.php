<?php
namespace Omnyfy\Easyship\Block\Adminhtml\Order;

class OrderView extends \Magento\Framework\View\Element\Template
{
    protected $backendSession;
    protected $apiHelper;
    protected $order;
    protected $vendorFactory;
    protected $locationFactory;
    protected $easyVendorLocFactory;
    protected $courierCollectionFactory;
    protected $priceHelper;
    protected $sourceCollectionFactory;
    protected $vSourceStockResource;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Model\Session $backendSession,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $order,
        \Omnyfy\Easyship\Model\EasyshipVendorFactory $vendorFactory,
        \Omnyfy\Vendor\Model\LocationFactory $locationFactory,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipSelectedCourier\CollectionFactory $courierCollectionFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->backendSession = $backendSession;
        $this->apiHelper = $apiHelper;
        $this->order = $order;
        $this->vendorFactory = $vendorFactory;
        $this->locationFactory = $locationFactory;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->courierCollectionFactory = $courierCollectionFactory;
        $this->priceHelper = $priceHelper;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vSourceStockResource = $vSourceStockResource;
    }

    public function getVendorLocations($orderId){
        #get selected courier and total charge
        #get vendor location that `Use Live Rate = No` to show button
        $order = $this->order->get($orderId);
        $quoteId = $order->getQuoteId();
        $vendorInfo = $this->backendSession->getVendorInfo();
        $arrCourier = [];
        $arrSource = [];
        $arrSourceStockId = [];
        $i = 0;

        $source = $this->sourceCollectionFactory->create();

        foreach ($order->getAllItems() as $item) {
            $sourceStockId = $item->getSourceStockId();
            if($this->getCalculateShippingBy() == 'overall_cart'){
                $sourceStockId = $this->getOverallPickupLocation();
            }

            if (!in_array($sourceStockId, $arrSourceStockId)) {
                // $location = $this->locationFactory->create()->load($locationId);
                $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
                $source = $this->sourceCollectionFactory->create()->getItemById($sourceCode);
                $vendorId = $item->getVendorId();
                $vendor = $this->vendorFactory->create()->load($vendorId);
                
                $couriers = $this->courierCollectionFactory->create()
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->addFieldToFilter('source_stock_id', $sourceStockId);

                if ($couriers->count() > 0) {
                    if ($couriers->getFirstItem()->getCourierId() != null) {
                        #show selected courier
                        $courierData = $couriers->getFirstItem();
                        $arrCourier[$i]['courier_name'] = $courierData->getCourierName();
                        $arrCourier[$i]['total_charge'] = $this->priceHelper->currency($courierData->getTotalCharge(), true, false);
                        $arrCourier[$i]['location_name'] = $source->getName();
                        $arrCourier[$i]['vendor_name'] = $vendor->getName();
                        $arrCourier[$i]['customer_paid'] = $courierData->getCustomerPaid();
                    }else{
                        #show selected courier button
                        // $account = $easyVendorLoc->getLocationAccount($locationId);
                        $source = $this->sourceCollectionFactory->create()->getItemById($sourceCode);

                        if (isset($source) && $source->getUseLiveRate()==0) {
                            if (isset($vendorInfo['vendor_id'])) {
                                #if vendor logged in, show only their select courier button
                                if ($vendorInfo['vendor_id'] == $vendorId) {
                                    $arrSource[$i]['source_stock_id'] = $sourceStockId;
                                    $arrSource[$i]['location_name'] = $source->getName();
                                    $arrSource[$i]['vendor_id'] = $vendorId;
                                    $arrSource[$i]['vendor_name'] = $vendor->getName();
                                }
                            }else{
                                #show all sellect courier button for MO
                                $arrSource[$i]['source_stock_id'] = $sourceStockId;
                                $arrSource[$i]['location_name'] = $source->getName();
                                $arrSource[$i]['vendor_id'] = $vendorId;
                                $arrSource[$i]['vendor_name'] = $vendor->getName();
                            }
                            $courierData = $couriers->getFirstItem();
                            $arrSource[$i]['total_charge'] = $this->priceHelper->currency($courierData->getTotalCharge(), true, false);
                            $arrSource[$i]['customer_paid'] = $courierData->getCustomerPaid();
                            $arrSource[$i]['courier_name'] = $courierData->getCourierName();
                        }
                    }
                }
                array_push($arrSourceStockId, $sourceStockId);
                $i++;
            }
        }
        $return['location'] = $arrSource;
        $return['courier'] = $arrCourier;
        return $return;
    }

    public function isEasyshipEnabled(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->_scopeConfig->getValue('carriers/easyship/active', $storeScope);
        return $enable;
    }

    public function getCalculateShippingBy(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $calculate = $this->_scopeConfig->getValue('omnyfy_vendor/vendor/calculate_shipping_by', $storeScope);
        return $calculate;
    }

    public function getOverallPickupLocation(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $location = $this->_scopeConfig->getValue('omnyfy_vendor/vendor/overall_pickup_location', $storeScope);
        return $location;
    }

    public function getConvertRate($rate){
        return $this->priceHelper->currency($rate, true, false);
    }
}