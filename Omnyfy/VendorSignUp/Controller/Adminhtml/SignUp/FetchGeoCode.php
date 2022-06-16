<?php

namespace Omnyfy\VendorSignUp\Controller\Adminhtml\SignUp;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\VendorSearch\Helper\MapSearchData;
use Omnyfy\VendorSignUp\Helper\Data;

class FetchGeoCode extends Action implements HttpPostActionInterface
{

    private $dataHelper;
    private $curl;
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['fetchgeocode'];
    private JsonFactory $resultJsonFactory;
    private MapSearchData $mapSearchData;
    private VendorRepositoryInterface $vendorRepository;

    public function __construct(
        Data $dataHelper,
        Curl $curl,
        JsonFactory $resultJsonFactory,
        MapSearchData $mapSearchData,
        VendorRepositoryInterface $vendorRepository,
        Context $context
    )
    {
        $this->dataHelper = $dataHelper;
        $this->curl = $curl;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->mapSearchData = $mapSearchData;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->_request->getParams();
        $address = !empty($params['address']) ? urlencode($params['address']) : '';
//        $vendorId = !empty($params['vendorId']) ? $params['vendorId'] : null;
        $ggApiKey = $this->dataHelper->getGoogleApiKey();
        $endpoint = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$ggApiKey";
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/gg_geocode_api.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        try{
            $this->curl->addHeader("Content-Type", "application/json");
            $this->curl->get($endpoint);
            $bodyResponse = $this->mapSearchData->jsonDecode($this->curl->getBody());
            if(!empty($bodyResponse['results'][0]['geometry']['location'])){
                $location = $bodyResponse['results'][0]['geometry']['location'];
                $addressLat = $location['lat'];
                $addressLng = $location['lng'];
//                if(isset($vendorId)){
//                    $vendor = $this->vendorRepository->getById($vendorId);
//                    $vendor->setData('latitude', $addressLat);
//                    $vendor->setData('longitude', $addressLng);
//                    $vendor->save();
//                }
                $data = $this->mapSearchData->jsonEncode(['addressLat' => $addressLat, 'addressLng' => $addressLng]);
            }else{
                $data = '{"error":"Something went wrong"}';
            }
        }catch (\Exception $e){
            $logger->info($e->getMessage());
            $data = '{"error":"Something went wrong"}';
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($data);
        return $result;
    }


    public function _processUrlKeys()
    {
        return true;
    }
}