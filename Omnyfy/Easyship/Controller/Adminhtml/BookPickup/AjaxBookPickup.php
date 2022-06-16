<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\BookPickup;

class AjaxBookPickup extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $scopeConfig;
    protected $easyVendorLocFactory;
    protected $apiHelper;
    protected $pickupFactory;
    protected $shipmentPickupFactory;
    protected $sourceCollectionFactory;
    protected $vSourceStockResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Omnyfy\Easyship\Model\EasyshipPickupFactory $pickupFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentPickupFactory $shipmentPickupFactory,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->apiHelper = $apiHelper;
        $this->pickupFactory = $pickupFactory;
        $this->shipmentPickupFactory = $shipmentPickupFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vSourceStockResource = $vSourceStockResource;
    }

    public function execute(){
        $token = null;
        $data['error'] = false;
        $data['message'] = null;

        $requestData = $this->getRequest()->getParams();
        $sourceStockId = $requestData['source_stock_id'];
        $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
        
        $source = $this->sourceCollectionFactory->create()->getSourceAccount($sourceCode);
        if ($source != null) {
            $token = $source->getAccessToken();
        }

        if($token){
            $params['courier_id'] = $requestData['courier_id'];
            $params['preferred_date'] = $requestData['preferred_date'];
            $params['preferred_max_time'] = $requestData['preferred_date'].'T'.$requestData['preferred_maxtime'].':00';
            $params['preferred_min_time'] = $requestData['preferred_date'].'T'.$requestData['preferred_mintime'].':00';
            $shipmentIds = explode(',', substr($requestData['shipment_ids'], 0, -1));
            $params['easyship_shipment_ids'] = $shipmentIds;

            try {
                $pickup = $this->apiHelper->bookPickup($token, json_encode($params));
                $arrPickup = json_decode($pickup, true);
                
                if(isset($arrPickup['errors']) && isset($arrPickup['message'])){
                    $data['error'] = true;
                    $data['message'] = $arrPickup['message'];
                    $this->messageManager->addError(__($arrPickup['message'].": ".$arrPickup['errors'][0]));
                }else if (isset($arrPickup['pickup'])) {
                    $pickupModel = $this->pickupFactory->create();
                    $pickupModel->setPickupId($arrPickup['pickup']['easyship_pickup_id']);
                    $pickupModel->setPickupReferenceNumber($arrPickup['pickup']['pickup_reference_number']);
                    $pickupModel->setPickupState($arrPickup['pickup']['pickup_state']);
                    $pickupModel->setPickupFee($arrPickup['pickup']['pickup_fee']);
                    $pickupModel->save();
                    $pickupId = $pickupModel->getId();

                    foreach ($shipmentIds as $shipId) {
                        $shipmentPickupModel = $this->shipmentPickupFactory->create();
                        $shipmentPickupModel->setEasyshipShipmentId($shipId);
                        $shipmentPickupModel->setPickupId($pickupId);
                        $shipmentPickupModel->save();
                    }
                    $successmessage = __('Pickup has been booked for Shipping Ids [%1].', substr($requestData['shipment_ids'], 0, -1));
                    $this->messageManager->addSuccess($successmessage);
                }

            } catch (\Exception $e) {
                $data['error'] = true;
                $data['message'] = $e->getMessage();
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
        else{
            $data['error'] = true;
            $data['message'] = 'No access token found';
            $this->messageManager->addError(__('No access token found'));
        }
        return $this->jsonFactory->create()->setData($data);
    }
}
