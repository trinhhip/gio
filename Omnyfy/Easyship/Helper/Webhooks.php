<?php
namespace Omnyfy\Easyship\Helper;

use Omnyfy\Easyship\Model\EasyshipShipmentLabelFactory;
use Omnyfy\Easyship\Model\EasyshipAccountFactory;	
use Omnyfy\Easyship\Model\EasyshipShipmentFactory;
use Omnyfy\Easyship\Exception\WebhookException;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http as Response;
use Firebase\JWT\JWT;

class Webhooks 
{
    /**
     * @var EasyshipShipmentLabelFactory
     */
    protected $shipmentLabelFactory;

    /**
     * @var EasyshipAccountFactory
     */
    protected $shipmentAccountFactory;

    /**
     * @var EasyshipShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var JWT
     */
    protected $jwt;

    /**
     * @param EasyshipShipmentLabelFactory $shipmentLabelFactory
     * @param EasyshipAccountFactory $shipmentAccountFactory
     * @param EasyshipShipmentFactory $shipmentFactory
     * @param Request $request
     * @param Response $response
     * @param JWT $jwt
     */
    public function __construct(
        EasyshipShipmentLabelFactory $shipmentLabelFactory,
        EasyshipAccountFactory $shipmentAccountFactory,
        EasyshipShipmentFactory $shipmentFactory,
        Request $request,
        Response $response,
        JWT $jwt
    )
    {
        $this->shipmentLabelFactory = $shipmentLabelFactory;
        $this->shipmentAccountFactory = $shipmentAccountFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->request = $request;
        $this->response = $response;
        $this->jwt = $jwt;
    }

    /**
     * @throws WebhookException
     */
    public function dispatchEvent($account = null){ 
        try {
            // Retrieve the request's body and parse it as JSON
            $body = $this->request->getContent();
            $data = json_decode($body,true);
            $this->log('data respone = '.print_r($data,true));
            $hmac_header = $_SERVER['HTTP_X_EASYSHIP_SIGNATURE'];
            if (isset($hmac_header)){ 
                $verified = $this->verifyWebhookSignature($body, $hmac_header, $account);  
                if($verified){
                    $easyshipShipmentId = '';
                    $labelUrl = '';
                    $trackingNumber = '';
                    $trackingPageUrl = '';
                    $isLabel = false;
                    $isShipment = false;
                    if($data['event_type'] == 'shipment.label.created'){
                        $isLabel = true;
                        $easyshipShipmentId = (!empty($data['label']['easyship_shipment_id']) ? $data['label']['easyship_shipment_id'] : '');
                        $status = (!empty($data['label']['status']) ? $data['label']['status'] : '');
                        $labelUrl = (!empty($data['label']['label_url']) ? $data['label']['label_url'] : '');
                        $trackingNumber = (!empty($data['label']['tracking_number']) ? $data['label']['tracking_number'] : '');
                        $trackingPageUrl = (!empty($data['label']['tracking_page_url']) ? $data['label']['tracking_page_url'] : '');
                    }elseif($data['event_type'] == 'shipment.label.failed'){
                        $isLabel = true;
                        $easyshipShipmentId = (!empty($data['label']['easyship_shipment_id']) ? $data['label']['easyship_shipment_id'] : '');
                        $status = (!empty($data['label']['status']) ? $data['label']['status'] : '');
                    }elseif($data['event_type'] == 'shipment.cancelled'){
                        $isShipment = true;
                        $easyshipShipmentId = (!empty($data['shipment']['easyship_shipment_id']) ? $data['shipment']['easyship_shipment_id'] : '');
                        $status = (!empty($data['shipment']['status']) ? $data['shipment']['status'] : '');
                    }elseif($data['event_type'] == 'shipment.tracking.status.changed'){
                        $isShipment = true;
                        $easyshipShipmentId = (!empty($data['tracking_status']['easyship_shipment_id']) ? $data['tracking_status']['easyship_shipment_id'] : '');
                        $status = (!empty($data['tracking_status']['status']) ? $data['tracking_status']['status'] : '');
                    }

                    if(!empty($easyshipShipmentId)){
                        if($isShipment){
                            $this->updateShipment($easyshipShipmentId,$status,$labelUrl,$trackingNumber,$trackingPageUrl);
                        }elseif($isLabel){
                            $this->updateLabel($easyshipShipmentId,$status,$labelUrl,$trackingNumber,$trackingPageUrl);
                        }
                    }
                }else{
                    throw new WebhookException("Webhook Secret Key Is Empty", 400);
                }
            }else{
                throw new WebhookException("Webhook signature could not be found in the request delivery sherpa", 400);
            }

        }
        catch (WebhookException $e)
        {
            $this->error($e->getMessage(), $e->statusCode);
        }
        catch (\Exception $e)
        {
            $this->log($e->getMessage());
            $this->log($e->getTraceAsString());
            $this->error($e->getMessage());
        }
    }

    protected function updateShipment($easyshipShipmentId,$status,$labelUrl,$trackingNumber,$trackingPageUrl)
    {
        try
        {
            $collection = $this->shipmentFactory->create()->getCollection()
                        ->addFieldToFilter('easyship_shipment_id',["eq"=>$easyshipShipmentId])
                        ->getFirstItem();

            if($collection->getEasyshipShipmentId()){
                $data = [
                    'easyship_shipment_id'=>$collection->geteasyshipShipmentId(),
                    'status'=>$status
                ];

                $shipmentData = $this->shipmentFactory->create();
                $shipmentData->load($collection->geteasyshipShipmentId());
                $shipmentData->setData($data);
                $shipmentData->save();

                return true;
            }
        }
        catch(\Exception $e)
        {
            $this->log($e->getMessage());
            $this->error($e->getMessage());
        }

        return false;
    }

    protected function updateLabel($easyshipShipmentId,$status,$labelUrl,$trackingNumber,$trackingPageUrl)
    {
        try
        {
            $collection = $this->shipmentLabelFactory->create()->getCollection()
                        ->addFieldToFilter('easyship_shipment_id',["eq"=>$easyshipShipmentId])
                        ->getFirstItem();
                        
            if($collection->getEasyshipShipmentId()){
                if($trackingNumber == "" && $trackingPageUrl == "" && $labelUrl == ""){
                    $data = [
                        'entity_id'=>$collection->getEntityId(),
                        'status'=>$status
                    ];
                }else{
                    $data = [
                        'entity_id'=>$collection->getEntityId(),
                        'easyship_shipment_id'=>$collection->getEasyshipShipmentId(),
                        'label_url'=> $labelUrl,
                        'status'=>$status,
                        'tracking_number'=>$trackingNumber,
                        'tracking_page_url'=>$trackingPageUrl
                    ];
                }

                $shipmentLabelData = $this->shipmentLabelFactory->create();
                $shipmentLabelData->load($collection->getEntityId());
                $shipmentLabelData->setData($data);
                $shipmentLabelData->save();

                return true;
            }
        }
        catch(\Exception $e)
        {
            $this->log($e->getMessage());
            $this->error($e->getMessage());
        }

        return false;
    }
    
    /**
     * @param $data
     * @throws WebhookException
     */
    protected function verifyWebhookSignature($data,$hmac_header, $account)
    {
        $signingSecret = $this->shipmentAccountFactory->create()->getCollection()
                        ->addFieldToFilter('entity_id',["eq"=>$account])
                        ->getFirstItem();

        if (empty($signingSecret->getWebhookSecretKey())) 
            return;

        try
        { 
            $decoded = $this->jwt->decode($hmac_header, $signingSecret->getWebhookSecretKey(), array('HS256'));
            return $decoded;
        }
        catch(\UnexpectedValueException $e)
        {
            throw new WebhookException("Invalid verify webhook signature", 400);
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

        $this->response
            ->setStatusCode($responseStatus)
            ->setContent($msg);

        $this->log("$responseStatus $msg");
    }

    /**
     * @param $msg
     */
    protected function log($msg)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/webhook_easyship.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($msg);
    }
}