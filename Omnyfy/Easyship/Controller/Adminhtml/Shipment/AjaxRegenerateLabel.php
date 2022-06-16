<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Shipment;

class AjaxRegenerateLabel extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $easyVendorLocFactory;
    protected $labelFactory;
    protected $apiHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentLabelFactory $labelFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->labelFactory = $labelFactory;
        $this->apiHelper = $apiHelper;
    }

    public function execute(){
        $token = null;
        $data['message'] = '';
        $data['error'] = false;

        $vendorLocationId = $this->getRequest()->getParam('location_id');
        $locationAcc = $this->easyVendorLocFactory->create()->getLocationAccount($vendorLocationId);
        if ($locationAcc != null) {
            $token = $locationAcc->getAccessToken();
        }

        if ($token) {
            $params['shipments'][]['easyship_shipment_id'] = $this->getRequest()->getParam('easyship_shipment_id');
            $params['shipments'][]['courier_id'] = $this->getRequest()->getParam('courier_id');

            try {
                $label = $this->apiHelper->regenerateLabel($token, json_encode($params));
                $labelData = json_decode($label, true);
                if (isset($labelData['labels']) && count($labelData['labels']) > 0) {
                    foreach ($labelData['labels'] as $value) {
                        $labelModel = $this->labelFactory->create();
                        $labelModel->setEasyshipShipmentId($value['easyship_shipment_id']);
                        $labelModel->setLabelState($value['label_state']);
                        $labelModel->setLabelUrl($value['label_url']);
                        $labelModel->setStatus($value['status']);
                        $labelModel->setTrackingNumber($value['tracking_number']);
                        $labelModel->setTrackingPageUrl($value['tracking_page_url']);
                        $labelModel->setCreatedAt(date('Y-m-d H:i:s'));
                        $labelModel->save();
                    }
                    $this->messageManager->addSuccess(__('Easyship Shipment Label has been generated'));

                }elseif(isset($labelData['errors']) && count($labelData['errors']) > 0){
                    $data['error'] = true;
                    foreach ($labelData['errors'] as $error) {
                        $data['message'] .= $error .'. ';
                    }
                }            
            } catch (\Exception $e) {
                $data['error'] = true;
                $data['message'] = $e->getMessage();
            }
        }else{
            $data['error'] = true;
            $data['message'] = 'No access token found';
        }

        return $this->jsonFactory->create()->setData($data);
    }
}