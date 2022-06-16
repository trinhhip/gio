<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Shipment;

class AjaxCancelShipment extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $easyVendorLocFactory;
    protected $shipFactory;
    protected $apiHelper;
    protected $sourceCollectionFactory;
    protected $vSourceStockResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentFactory $shipFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->shipFactory = $shipFactory;
        $this->apiHelper = $apiHelper;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vSourceStockResource = $vSourceStockResource;
    }

    public function execute(){
        $token = null;
        $data['message'] = '';
        $data['error'] = false;

        $sourceStockId = $this->getRequest()->getParam('source_stock_id');
        $easyshipShipmentId = $this->getRequest()->getParam('easyship_shipment_id');
        $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
        $source = $this->sourceCollectionFactory->create()->getSourceAccount($sourceCode);
        if ($source != null) {
            $token = $source->getAccessToken();
        }

        if ($token) {
            try {
                $shipmentCancel = $this->apiHelper->cancelShipment($token, $easyshipShipmentId);
                $shipmentData = json_decode($shipmentCancel, true);

                if (isset($shipmentData['shipment'])) {
                    $shipmentModel = $this->shipFactory->create();
                    $shipmentModel->load($shipmentData['shipment']['easyship_shipment_id']);
                    $shipmentModel->setStatus('cancelled');
                    $shipmentModel->save();

                    $msg = $shipmentData['shipment']['message'];
                    $data['error'] = false;
                    $data['message'] = $msg;
                    $this->messageManager->addSuccess(__($msg));
                }else{
                    if(isset($shipmentData['error'])){ // Couldn't find Shipment
                        $msg = $shipmentData['error'];
                        $data['error'] = true;
                        $data['message'] = $msg;
                        $this->messageManager->addError(__($msg));
                    }elseif(count($shipmentData)>0){ // already been cancelled
                        $msg = "";
                        foreach (($shipmentData) as $error) {
                            $msg .= $error .". ";
                        }
                        $data['error'] = true;
                        $data['message'] = $msg;
                        $this->messageManager->addError(__($msg));
                    }else{
                        $msg = "Error: Server Error";
                        $data['error'] = true;
                        $data['message'] = $msg;
                        $this->messageManager->addError(__($msg));
                    }
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
