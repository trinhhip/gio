<?php
namespace Omnyfy\Easyship\Helper;

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $curl;
    protected $accountFactory;
    protected $httpClientFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountFactory,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ){
        parent::__construct($context);
        $this->curl = $curl;
        $this->accountFactory = $accountFactory;
        $this->httpClientFactory = $httpClientFactory;
    }

    public function getShippingCategory(){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_shipping_category.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $account = $this->accountFactory->create()->getDefaultMOAccount();
        $result = null;
        
        if (isset($account)) {
            try {
                $url = "https://api.easyship.com/reference/v1/categories";
                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer ".$account->getAccessToken());
                $this->curl->get($url);
                $result = $this->curl->getBody();
                $logger->info($result);
            } catch (\Exception $e) {
                $logger->info($e->getMessage());
                $result = '{"error":"Something went wrong"}';
            }
        }else{
            $result = '{"error":"No MO\'s bearer token found."}';
            $logger->info('No MO\'s bearer token found.');
        }
        return $result;
    }

    public function getLiveRates($token, $params){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_live_rates.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = null;

        try {
            $url = "https://api.easyship.com/rate/v1/rates";
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer ".$token);
            $this->curl->post($url, $params);
            $result = $this->curl->getBody();
            $arrResult = json_decode($result, true);
            // $logger->info($result);

            if (array_key_exists('messages', $arrResult) || array_key_exists('message', $arrResult)) {
                $logger->info($params);
                $logger->info($result);
            }
        } catch (\Exception $e) {
            $logger->info($e->getMessage());
        }
        return $result;
    }

    public function createShipmentAndBuyLabel($token, $params){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_createshipment.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = null;

        try {
            $url = "https://api.easyship.com/shipment/v1/shipments/create_and_buy_label";
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer ".$token);
            $this->curl->post($url, $params);
            $result = $this->curl->getBody();

            // $logger->info($params);
            // $logger->info($result);

        } catch (\Exception $e) {
            $logger->info($params);
            $logger->info($result);
            $logger->info($e->getMessage());
        }
        return $result;
    }

    public function getPickupSlot($token, $courierId){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_pickupslot.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = null;

        try {
            $url = "https://api.easyship.com/pickup/v1/pickup_slots/". $courierId;
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer ".$token);
            $this->curl->get($url);
            $result = $this->curl->getBody();

            // $logger->info($result);

        } catch (\Exception $e) {
            $logger->info($e->getMessage());
        }
        return $result;
    }

    public function bookPickup($token, $params){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_bookpickup.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = null;

        try {
            $url = "https://api.easyship.com/pickup/v1/pickups";
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer ".$token);
            $this->curl->post($url, $params);
            $result = $this->curl->getBody();

            $logger->info($params);
            $logger->info($result);

        } catch (\Exception $e) {
            $logger->info($params);
            $logger->info($result);
            $logger->info($e->getMessage());
        }
        return $result;
    }

    public function regenerateLabel($token, $params){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_regeneratelabel.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = null;

        try {
            $url = "https://api.easyship.com/label/v1/labels";
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer ".$token);
            $this->curl->post($url, $params);
            $result = $this->curl->getBody();

            // $logger->info($params);
            // $logger->info($result);

        } catch (\Exception $e) {
            $logger->info($params);
            $logger->info($result);
            $logger->info($e->getMessage());
        }
        return $result;
    }

    public function markAsHandedOver($token, $params){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_markhandedover.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = null;

        try {
            $url = "https://api.easyship.com/pickup/v1/direct_handover";
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer ".$token);
            $this->curl->post($url, $params);
            $result = $this->curl->getBody();

            // $logger->info($params);
            // $logger->info($result);

        } catch (\Exception $e) {
            $logger->info($params);
            $logger->info($result);
            $logger->info($e->getMessage());
        }
        return $result;
    }

    public function cancelShipment($token, $param){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_cancel_shipment.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = null;
        
        try {
            $url = "https://api.easyship.com/shipment/v1/shipments/".$param."/cancel";

            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->addHeader("Authorization", "Bearer ".$token);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'PATCH');
            $this->curl->get($url);
            $result = $this->curl->getBody();
            // $logger->info($param);
            // $logger->info($result);
        } catch (\Exception $e) {
            $logger->info($params);
            $logger->info($result);
            $logger->info($e->getMessage());
        }
        return $result;
    }

    public function saveShippingAddress($token, $param, $addressId = null){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/api_easyship_address.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $result = null;

        try{
            $url = "https://api.easyship.com/address/v1/addresses";
            $logger->info($url);

            if ($addressId) {
                // update shipping address
                $url .= "/".$addressId;
                $authorization = "Authorization: Bearer ".$token;
                $logger->info($url);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $authorization));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
                $result = curl_exec($ch);
                curl_close ($ch);

                // $logger->info($param);
                // $logger->info($result);

            }else{
                // add new shipping address
                $logger->info($url);

                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", "Bearer ".$token);
                $this->curl->post($url, $param);
                $result = $this->curl->getBody();

                // $logger->info($param);
                // $logger->info($result);
            }
        } catch (\Exception $e) {
            $logger->info($param);
            $logger->info($result);
            $logger->info($e->getMessage());
        }
        return $result;
    }

    public function convertUnit($value, $isWeightUnit){
        $newvalue = null;
        if ($isWeightUnit) {
            //convert lbs to kg
            $newvalue = $value * 0.454;
        }else{
            //convert inch to cm
            $newvalue = $value * 2.54;
        }
        return $newvalue;
    }

    public function getProductDimensionsByOrderItem($orderItem){
        $product = $orderItem->getProduct();

        $dimensions['height'] = $product->getData('omnyfy_dimensions_height');
        $dimensions['width'] = $product->getData('omnyfy_dimensions_width');
        $dimensions['length'] = $product->getData('omnyfy_dimensions_length');

        if ($orderItem->getProductType() == "configurable") {
            $childItems = $orderItem->getChildrenItems();
            if (count($childItems)) {
                foreach($orderItem->getChildrenItems() as $chd){
                    $childProd = $chd->getProduct();
                    $dimensions['height'] = $childProd->getData('omnyfy_dimensions_height');
                    $dimensions['width'] = $childProd->getData('omnyfy_dimensions_width');
                    $dimensions['length'] = $childProd->getData('omnyfy_dimensions_length');
                }
            }
        }
        return $dimensions;
    }
}
