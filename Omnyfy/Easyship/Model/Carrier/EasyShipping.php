<?php
namespace Omnyfy\Easyship\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class EasyShipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

    /**
     * @var string
     */
    protected $_code = 'easyship';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory
     */
    protected $_easyshipVendorLocFactory;

    /**
     * @var \Omnyfy\Easyship\Model\EasyshipRateOptionFactory
     */
    protected $_easyshipRateOptionFactory;

    /**
     * @var \Omnyfy\Easyship\Helper\Api
     */
    protected $_apiHelper;

    /**
     * @var \Omnyfy\Vendor\Model\LocationFactory
     */
    protected $_locationFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $_response;

    /**
     * @var \Omnyfy\Easyship\Helper\Config
     */
    protected $_config;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory
     */
    protected $_rateErrorFactory;

    /**
     *  @var \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory
     */
    protected $sourceCollectionFactory;

    /**
     *  @var \Omnyfy\Vendor\Model\Resource\VendorSourceStock
     */
    protected $vSourceStockResource;

    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory        $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                          $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                        $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory       $ateMethodFactory
     * @param \Magento\Checkout\Model\Cart                                      $cartModel
     * @param \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory              $easyshipVendorLocFactory
     * @param \Omnyfy\Easyship\Model\EasyshipRateOptionFactory                  $easyshipRateOptionFactory
     * @param \Omnyfy\Easyship\Helper\Api                                       $apiHelper
     * @param \Omnyfy\Vendor\Model\LocationFactory                              $locationFactory
     * @param \Magento\Store\Model\StoreManagerInterface                        $storeManager
     * @param \Magento\Catalog\Model\ProductFactory                             $productFactory
     * @param \Magento\Framework\Pricing\Helper\Data                            $priceHelper
     * @param \Magento\Framework\App\Response\Http                              $response
     * @param \Omnyfy\Easyship\Helper\Config                                    $config
     * @param \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory   $sourceCollectionFactory
     * @param array                                                             $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Cart $cartModel,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyshipVendorLocFactory,
        \Omnyfy\Easyship\Model\EasyshipRateOptionFactory $easyshipRateOptionFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Omnyfy\Vendor\Model\LocationFactory $locationFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\App\Response\Http $response,
        \Omnyfy\Easyship\Helper\Config $config,
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_cart = $cartModel;
        $this->_easyshipVendorLocFactory = $easyshipVendorLocFactory;
        $this->_easyshipRateOptionFactory = $easyshipRateOptionFactory;
        $this->_apiHelper = $apiHelper;
        $this->_locationFactory = $locationFactory;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_priceHelper = $priceHelper;
        $this->_response = $response;
        $this->_config = $config;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vSourceStockResource = $vSourceStockResource;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (empty($request->getDestPostcode()) || empty($request->getDestCity())) {
            $errorMessage = [
                'error' => true,
                'message' => __('Please enter your post code and city to get shipping rates')
            ];

            $error = $this->_throwError($errorMessage);
            return $error;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        $liveRatesData = "";

        $items = $request->getAllItems();
        if (empty($items)) {
            return false;
        }

        /** @var \Magento\Quote\Model\Quote\Item $firstItem */
        $firstItem = reset($items);
        if (!$firstItem) {
            return false;
        }

        $quote = $firstItem->getQuote();
        if (!($quote instanceof \Magento\Quote\Model\Quote)) {
            return false;
        }

        $items = $quote->getAllVisibleItems();

        $sourceStockId = $request->getSourceStockId();
        $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);

        $locationId = $request->getLocationId();
        if ($this->_config->getCalculateShippingBy() == 'overall_cart') {
            $sourceStockId = $this->_config->getOverallPickupLocation();
        }

        $source = $this->sourceCollectionFactory->create()->getAccountRateOptionBySource($sourceCode);
        if ($source != null) {
            if($source->getUseLiveRate()){ // live rate
                $liveRatesData = $this->getLiveRates($items, $request, $source);
            }elseif(!$source->getUseLiveRate()){ // fixed rate
                $productModel = $this->_productFactory->create();
                $requestItems = $request->getData('all_items');
                foreach ($requestItems as $item) {
                    $product = $productModel->load($item->getproduct()->getEntityId());
                    $odHeight = $product->getData('omnyfy_dimensions_height');
                    $odWidth = $product->getData('omnyfy_dimensions_width');
                    $odLength = $product->getData('omnyfy_dimensions_length');
                    $weight = $product->getData('weight');
                    $shippingCategory = $product->getData('easyship_shipping_category');
                    if (empty($odHeight) || empty($odLength) || empty($odWidth)) {
                        $errorMessage = [
                            'error' => true,
                            'message' => sprintf('Rates could not be calculated: Items should have dimensions. If the box dimensions are provided, then dimensions for items can be optional.')
                        ];

                        $error = $this->_throwError($errorMessage);
                        return $error;
                    } elseif (empty($shippingCategory)) {
                        $errorMessage = [
                            'error' => true,
                            'message' => sprintf('Sorry, we couldn\'t find any shipping solutions based on the information provided.')
                        ];

                        $error = $this->_throwError($errorMessage);
                        return $error;
                    } elseif (empty($weight)) {
                        $errorMessage = [
                            'error' => true,
                            'message' => sprintf('Rates could not be calculated: Items should have a weight. If the total actual weight are provided, then weight for items can be optional.')
                        ];

                        $error = $this->_throwError($errorMessage);
                        return $error;
                    }
                }

                $methodSet = $this->_code.$sourceStockId;
                $methodName = $this->_config->getPlatformName();
                $method = $this->_rateMethodFactory->create();
                $method->setCarrierTitle($methodName);
                $method->setPrice($source->getPriceRateOption());
                $method->setCost($source->getPriceRateOption());
                $method->setMethodTitle($source->getNameRateOption());
                $method->setCarrier($this->_code);
                $method->setMethod($methodSet);
                $result->append($method);
            }

            if(!empty($liveRatesData) ){ // live rate
                if(!isset($liveRatesData['error'])){
                    foreach($liveRatesData as $value) {
                        $moveSpace = str_replace('-', ' ', $value['courier_name']);
                        $courierName = preg_replace('/[^A-Za-z0-9\-]/', '', $moveSpace);
                        $methodSet = $this->_code.$locationId.$courierName;
                        $method = $this->_rateMethodFactory->create();
                        $method->setCarrierTitle($value['full_description']);
                        $method->setPrice($value['total_charge']);
                        $method->setCost($value['total_charge']);
                        $method->setMethodTitle($value['rate_option_name']);
                        $method->setCarrier($this->_code);
                        $method->setMethod($methodSet);
                        $method->setMethodDescription($value['courier_id']);
                        $result->append($method);
                    }
                }else{
                    $errorMessage = [
                        'error' => true,
                        'message' => sprintf($liveRatesData['message'])
                    ];

                    $error = $this->_throwError($errorMessage);
                    return $error;
                }
            }
        }

        return $result;
    }

    public function getLiveRates($items, $request, $source){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $taxPaidBy = $this->_scopeConfig->getValue('carriers/easyship/tax_paid_by', $storeScope);
        $isInsured = $this->_scopeConfig->getValue('carriers/easyship/include_insurance', $storeScope);
        $weightUnit = $this->_scopeConfig->getValue('general/locale/weight_unit', $storeScope);
        $params['origin_postal_code'] = $source->getPostcode();
        $params['origin_country_alpha2'] = $source->getCountryId();
        $params['destination_postal_code'] = $request->getDestPostcode();
        $params['destination_country_alpha2'] = $request->getDestCountryId();
        $params['destination_city'] = $request->getDestCity();
        $params['destination_address_line_1'] = $request->getDestStreet();
        $params['taxes_duties_paid_by'] = $taxPaidBy? ucfirst($taxPaidBy) : "Sender";
        $params['is_insured'] = $isInsured? "true" : "false";
        $params['output_currency'] = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $dataItems = [];

        foreach($items as $item) {
            if($this->_config->getCalculateShippingBy() == 'overall_cart' || $item->getSourceStockId() == $request->getSourceStockId()){
                $product = $this->_productFactory->create()->load($item->getProduct()->getId());
                $attr = $product->getResource()->getAttribute('easyship_shipping_category');
                $optionText = "";
                if ($attr->usesSource()) {
                    $optionText = $attr->getSource()->getOptionText($product->getData('easyship_shipping_category'));
                }

                $productHeight = $product->getData('omnyfy_dimensions_height');
                $productWidth = $product->getData('omnyfy_dimensions_width');
                $productLength = $product->getData('omnyfy_dimensions_length');

                if ($item->getProductType() == "configurable") {
                    $childId = $item->getOptionByCode('simple_product')->getValue();
                    $childProduct = $this->_productFactory->create()->load($childId);

                    $productHeight = $childProduct->getData('omnyfy_dimensions_height');
                    $productWidth = $childProduct->getData('omnyfy_dimensions_width');
                    $productLength = $childProduct->getData('omnyfy_dimensions_length');
                }

                $dataItems[] = [
                    'actual_weight' => $weightUnit == 'kgs' ? $item->getWeight() : $this->_apiHelper->convertUnit($item->getWeight(), 1),
                    'height' => $weightUnit == 'kgs' ? $productHeight : $this->_apiHelper->convertUnit($productHeight, 0),
                    'width' => $weightUnit == 'kgs' ? $productWidth : $this->_apiHelper->convertUnit($productWidth, 0),
                    'length' => $weightUnit == 'kgs' ? $productLength : $this->_apiHelper->convertUnit($productLength, 0),
                    "category" => $optionText,
                    "declared_currency" => $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
                    "declared_customs_value" => $item->getBasePrice(),
                    "quantity" => (int)$item->getQty()
                ];
            }
        }
        $params['items'] = $dataItems;
        $rates = $this->_apiHelper->getLiveRates($source->getAccessToken(), json_encode($params));
        $arrRates = json_decode($rates, true);
        $returnRates = [];
        $i = 0;
        if (isset($arrRates['rates'])) {
            if(empty($arrRates['rates'])){  // ex: too much Qty
                $this->log($arrRates);
                $returnRates = [
                    'error' => 1,
                    'message' => $arrRates['messages'][0]
                ];
            }else{
                foreach ($arrRates['rates'] as $value) {
                    $formattedCurrencyValue = $this->_priceHelper->currency($value['total_charge'], true, false);
                    $returnRates[$i]['courier_id'] = $value['courier_id'];
                    $returnRates[$i]['courier_name'] = $value['courier_name'];
                    $returnRates[$i]['total_charge'] = $value['total_charge'];
                    $returnRates[$i]['total_charge_currency'] = $formattedCurrencyValue;
                    $returnRates[$i]['rate_option_name'] = $source->getNameRateOption();
                    $returnRates[$i]['full_description'] = $value['full_description'];
                    $i++;
                }
            }
        }else{
            $this->log($arrRates);
            if(isset($arrRates['message'])){ // ex: weight OR dimensional is empty
                $returnRates = [
                    'error' => 1,
                    'message' => $arrRates['message']
                ];
                if (isset($arrRates['errors']) && count($arrRates['errors'])) {
                    $returnRates = [
                        'error' => 1,
                        'message' => $arrRates['message'].": ".$arrRates['errors'][0]
                    ];
                }
            }elseif(isset($arrRates['error'])){ // ex: invalid bearer token OR empty
                $returnRates = [
                    'error' => 1,
                    'message' => $arrRates['error']
                ];
            }else{
                    $returnRates = [
                        'error' => 1,
                        'message' => "We can't get rates."
                    ];
            }
        }

        return $returnRates;
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

        $this->_response
            ->setStatusCode($responseStatus)
            ->setContent($msg);

        $this->log("$responseStatus $msg");
    }

    /**
     * @param $msg
     */
    protected function log($msg)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/get-liverate.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($msg);
    }

    /**
     * @param array $data
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Error
     */
    protected function _throwError($data = []) {
        $message = null;
        if (isset($data['error']) && isset($data['message'])) {
            $message = $data['message'];
        }

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
        $error = $this->_rateErrorFactory->create(
            [
                'data' => [
                    'carrier' => $this->_code,
                    'carrier_title' => 'Easyship Delivery',
                    'error_message' => $message ? $message : $this->getConfigData('specificerrmsg'),
                ],
            ]
        );

        return $error;
    }

}
