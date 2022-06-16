<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Shipment;

class AjaxCreateShipmentBuyLabel extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $scopeConfig;
    protected $orderRepository;
    protected $easyVendorLocFactory;
    protected $shipFactory;
    protected $itemFactory;
    protected $labelFactory;
    protected $apiHelper;
    protected $regionFactory;
    protected $sourceCollectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentFactory $shipFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentItemFactory $itemFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentLabelFactory $labelFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->shipFactory = $shipFactory;
        $this->itemFactory = $itemFactory;
        $this->labelFactory = $labelFactory;
        $this->apiHelper = $apiHelper;
        $this->regionFactory = $regionFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
    }

    public function execute(){
        $token = null;
        $data['message'] = '';
        $data['error'] = false;

        $orderId = $this->getRequest()->getParam('order_id');
        $vendorId = $this->getRequest()->getParam('vendor_id');
        $sourceStockId = $this->getRequest()->getParam('source_stock_id');
        $courierId = $this->getRequest()->getParam('courier_id');
        $courierEntityId = $this->getRequest()->getParam('courier_entity_id');
        $sourceCode = $this->getRequest()->getParam('source_code');

        $source = $this->sourceCollectionFactory->create()->getSourceAccount($sourceCode);
        if ($source != null) {
            $token = $source->getAccessToken();
            $shipByMarketplace = $source->getCreatedByMo();
            $originAddressId = $source->getEasyshipAddressId();
        }

        if ($token) {
            $order = $this->orderRepository->get($orderId);
            $shippingAddress = $order->getShippingAddress();
            $regionCode = null;
            if ($shippingAddress->getRegionId()) {
                $region = $this->regionFactory->create()->load($shippingAddress->getRegionId());
                $regionCode = $region->getCode();
            }

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $taxPaidBy = $this->scopeConfig->getValue('carriers/easyship/tax_paid_by', $storeScope);
            $buyLabelSync = $this->scopeConfig->getValue('carriers/easyship/buy_label_sync', $storeScope);
            $weightUnit = $this->scopeConfig->getValue('general/locale/weight_unit', $storeScope);
            $calculateBy = $this->scopeConfig->getValue('omnyfy_vendor/vendor/calculate_shipping_by', $storeScope);
            $overallPickup = $this->scopeConfig->getValue('omnyfy_vendor/vendor/overall_pickup_location', $storeScope);

            if($originAddressId != null){
                // $params['origin_address_id'] = $originAddressId;
            }

            $params['platform_name'] = $this->scopeConfig->getValue('carriers/easyship/platform_name', $storeScope);
            $params['platform_order_number'] = $order->getIncrementId();
            $params['taxes_duties_paid_by'] = $taxPaidBy? ucfirst($taxPaidBy) : "Sender";
            $params['is_insured'] = $this->scopeConfig->getValue('carriers/easyship/include_insurance', $storeScope)? "true" : "false";
            $params['destination_country_alpha2'] = $shippingAddress->getCountryId();
            $params['destination_postal_code'] = $shippingAddress->getPostCode();
            $params['destination_city'] = $shippingAddress->getCity();
            $params['destination_state'] = $regionCode;
            $params['destination_name'] = substr($shippingAddress->getFirstname()." ".$shippingAddress->getLastname(), 0, 50);
            $params['destination_address_line_1'] = substr($shippingAddress->getStreet()[0], 0, 100);
            if(count($shippingAddress->getStreet())>1){
                $params['destination_address_line_2'] = substr($shippingAddress->getStreet()[1], 0, 100);
            }
            $params['destination_phone_number'] = $shippingAddress->getTelephone();
            $params['destination_email_address'] = $shippingAddress->getEmail();
            $params['selected_courier_id'] = $courierId;
            $params['allow_courier_fallback'] = $this->scopeConfig->getValue('carriers/easyship/allow_courier_fallback', $storeScope)? "true" : "false";
            $params['buy_label_synchronous'] = $buyLabelSync? "true" : "false";
            if ($buyLabelSync) {
                $params['format'] = $this->scopeConfig->getValue('carriers/easyship/buy_label_format', $storeScope);
                $params['label'] = $this->scopeConfig->getValue('carriers/easyship/label_size', $storeScope);
                $params['commercial_invoice'] = $this->scopeConfig->getValue('carriers/easyship/commercial_invoice', $storeScope);
                $params['packing_slip'] = $this->scopeConfig->getValue('carriers/easyship/packing_slip', $storeScope);
            }

            $dataItems = [];
            $dataShipmentItem = [];
            foreach($order->getAllVisibleItems() as $item) {
                if ($item->getIsVirtual()) {
                    continue;
                }
                if (($calculateBy == 'overall_cart' && $overallPickup == $sourceStockId) || $item->getSourceStockId() == $sourceStockId) {
                    $product = $item->getProduct();
                    $attr = $product->getResource()->getAttribute('easyship_shipping_category');
                    $optionText = "";
                    if ($attr->usesSource()) {
                        $optionText = $attr->getSource()->getOptionText($product->getData('easyship_shipping_category'));
                    }

                    $dimensions = $this->apiHelper->getProductDimensionsByOrderItem($item);

                    $dataItems[] = [
                        'description' => $product->getName()? substr($product->getName(), 0, 150) : substr($product->getShortDescription(), 0, 180),
                        'sku' => $item->getSku(),
                        'actual_weight' => $weightUnit == 'kgs' ? $item->getWeight() : $this->apiHelper->convertUnit($item->getWeight(), 1),
                        'height' => $weightUnit == 'kgs' ? $dimensions['height'] : $this->_apiHelper->convertUnit($dimensions['height'], 0),
                        'width' => $weightUnit == 'kgs' ? $dimensions['width'] : $this->_apiHelper->convertUnit($dimensions['width'], 0),
                        'length' => $weightUnit == 'kgs' ? $dimensions['length'] : $this->_apiHelper->convertUnit($dimensions['length'], 0),
                        "category" => $optionText,
                        "declared_currency" => $order->getOrderCurrencyCode(),
                        "declared_customs_value" => $item->getBasePrice(),
                        "quantity" => (int)$item->getQtyOrdered()
                    ];
                    $dataShipmentItem[] = [
                        'sku' => $item->getSku(),
                        'name' => $item->getName(),
                        'product_id' => $product->getId()
                    ];
                }
            }
            $params['items'] = $dataItems;

            try {
                $shipment = $this->apiHelper->createShipmentAndBuyLabel($token, json_encode($params));
                $shipmentData = json_decode($shipment, true);
                if (isset($shipmentData['shipment'])) {
                    if (isset($shipmentData['shipment']['label_response']['errors']) && count($shipmentData['shipment']['label_response']['errors'])>0) {
                        $msg = "";
                        foreach (($shipmentData['shipment']['label_response']['errors']) as $error) {
                            $msg .= $error .". ";
                        }
                        $data['error'] = true;
                        $data['message'] = $msg;
                        $this->messageManager->addError(__($msg." available balance ".$shipmentData['shipment']['label_response']['available_balance']));
                    }else{
                        $shipModel = $this->shipFactory->create();
                        $shipModel->setEasyshipShipmentId($shipmentData['shipment']['easyship_shipment_id']);
                        $shipModel->setDestinationName($shipmentData['shipment']['destination_name']);
                        $shipModel->setSourceStockId($sourceStockId);
                        $shipModel->setOrderId($orderId);
                        $shipModel->setSelectedCourierId($courierEntityId);
                        $shipModel->setCurrency($order->getOrderCurrencyCode());
                        $shipModel->setCourierDoesPickup($shipmentData['shipment']['selected_courier']['courier_does_pickup']);
                        $shipModel->setTotalCharge($shipmentData['shipment']['selected_courier']['total_charge']);
                        $shipModel->setCourierName($shipmentData['shipment']['selected_courier']['name']);
                        $shipModel->setCreatedAt(date('Y-m-d H:i:s'));
                        $shipModel->save();

                        foreach ($dataShipmentItem as $itemData) {
                            $itemModel = $this->itemFactory->create();
                            $itemModel->setEasyshipShipmentId($shipmentData['shipment']['easyship_shipment_id']);
                            $itemModel->setSku($itemData['sku']);
                            $itemModel->setName($itemData['name']);
                            $itemModel->setProductId($itemData['product_id']);
                            $itemModel->save();
                        }
                        if ($shipmentData['shipment']['label_state'] == "generated") {
                            $labelModel = $this->labelFactory->create();
                            $labelModel->setEasyshipShipmentId($shipmentData['shipment']['easyship_shipment_id']);
                            $labelModel->setLabelState($shipmentData['shipment']['label_state']);
                            $labelModel->setLabelUrl($shipmentData['shipment']['label_url']);
                            $labelModel->setStatus($shipmentData['shipment']['label_response']['status']);
                            $labelModel->setTrackingNumber($shipmentData['shipment']['tracking_number']);
                            $labelModel->setTrackingPageUrl($shipmentData['shipment']['tracking_page_url']);
                            $labelModel->setCreatedAt(date('Y-m-d H:i:s'));
                            $labelModel->save();
                        }

                        $orderShippingData = [
                            'order_id' => $orderId,
                            'vendor_id' => $vendorId,
                            'source_stock_id' => $sourceStockId,
                            'shipping_reference' => $courierId,
                            'method_title' => $shipmentData['shipment']['selected_courier']['name'],
                            'method_code' => $shipmentData['shipment']['selected_courier']['name'],
                            'ship_by_marketplace' => $shipByMarketplace,
                            'shipping_amount' => $shipmentData['shipment']['selected_courier']['total_charge'],
                        ];

                        $this->_eventManager->dispatch('omnyfy_vendor_create_shipment_after', ['shipment' => $orderShippingData]);

                        $data['error'] = false;
                        $data['message'] = 'Success';
                        $this->messageManager->addSuccess(__('Easyship Shipment has been booked'));
                    }
                }elseif(isset($shipmentData['error'])){
                    $data['error'] = true;
                    $data['message'] = $shipmentData['error'];
                    $this->messageManager->addError(__($shipmentData['error']));
                }
            } catch (\Exception $e) {
                $data['error'] = true;
                $data['message'] = $e->getMessage();
                $this->messageManager->addError(__($e->getMessage()));
            }
        }else{
            $data['error'] = true;
            $data['message'] = 'No access token found';
            $this->messageManager->addError(__('No access token found'));
        }

        return $this->jsonFactory->create()->setData($data);
    }
}
