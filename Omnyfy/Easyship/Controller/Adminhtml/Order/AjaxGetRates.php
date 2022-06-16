<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Order;

class AjaxGetRates extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $scopeConfig;
    protected $orderRepository;
    protected $easyVendorLocFactory;
    protected $locationFactory;
    protected $apiHelper;
    protected $priceHelper;
    protected $regionFactory;
    protected $vSourceStockResource;
    protected $sourceCollectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Vendor\Model\LocationFactory $locationFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->locationFactory = $locationFactory;
        $this->apiHelper = $apiHelper;
        $this->priceHelper = $priceHelper;
        $this->regionFactory = $regionFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vSourceStockResource = $vSourceStockResource;
    }

    public function execute(){
        $token = null;
        $data['error'] = false;
        $data['message'] = null;

        $orderId = $this->getRequest()->getParam('order_id');
        $country = $this->getRequest()->getParam('country');
        $postal = $this->getRequest()->getParam('postal');
        $city = $this->getRequest()->getParam('city');
        $sourceStockId = $this->getRequest()->getParam('location_id');
        $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);
        $source = $this->sourceCollectionFactory->create()->getSourceAccount($sourceCode);
        if ($source != null) {
            $token = $source->getAccessToken();
        }

        if ($token) {
            $order = $this->orderRepository->get($orderId);
            $currency = $order->getOrderCurrencyCode();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $taxPaidBy = $this->scopeConfig->getValue('carriers/easyship/tax_paid_by', $storeScope);
            $isInsured = $this->scopeConfig->getValue('carriers/easyship/include_insurance', $storeScope);
            $weightUnit = $this->scopeConfig->getValue('general/locale/weight_unit', $storeScope);
            $calculateBy = $this->scopeConfig->getValue('omnyfy_vendor/vendor/calculate_shipping_by', $storeScope);

            $regionName = $order->getShippingAddress()->getRegion();
            if ($regionName) {
                $region = $this->regionFactory->create()
                    ->addRegionNameFilter($regionName)
                    ->getFirstItem()
                    ->toArray();

                if (isset($region)) {
                    $params['destination_state'] = $region['code'];
                }
            }
            $params['destination_country_alpha2'] = $country;
            $params['destination_postal_code'] = $postal;
            $params['destination_city'] = $city;
            $params['origin_country_alpha2'] = $source->getCountryId();
            $params['origin_postal_code'] = $source->getPostcode();
            $params['taxes_duties_paid_by'] = $taxPaidBy? ucfirst($taxPaidBy) : "Sender";
            $params['is_insured'] = $isInsured? "true" : "false";
            $params['output_currency'] = $order->getOrderCurrencyCode();
            $dataItems = [];

            foreach($order->getAllVisibleItems() as $item) {
                if ($item->getQtyToShip() <= 0 || $item->getIsVirtual()) {
                    continue;
                }
                $itemSourceStockId = $item->getSourceStockId();
                if ($calculateBy == 'overall_cart'|| $itemSourceStockId == $sourceStockId) {
                    $product = $item->getProduct();
                    $attr = $product->getResource()->getAttribute('easyship_shipping_category');
                    $optionText = "";
                    if ($attr->usesSource()) {
                        $optionText = $attr->getSource()->getOptionText($product->getData('easyship_shipping_category'));
                    }

                    $dimensions = $this->apiHelper->getProductDimensionsByOrderItem($item);

                    $dataItems[] = [
                        'actual_weight' => $weightUnit == 'kgs' ? $item->getWeight() : $this->apiHelper->convertUnit($item->getWeight(), 1),
                        'height' => $weightUnit == 'kgs' ? $dimensions['height'] : $this->_apiHelper->convertUnit($dimensions['height'], 0),
                        'width' => $weightUnit == 'kgs' ? $dimensions['width'] : $this->_apiHelper->convertUnit($dimensions['width'], 0),
                        'length' => $weightUnit == 'kgs' ? $dimensions['length'] : $this->_apiHelper->convertUnit($dimensions['length'], 0),
                        "category" => $optionText,
                        "declared_currency" => $currency,
                        "declared_customs_value" => $item->getBasePrice(),
                        "quantity" => (int)$item->getQtyOrdered()
                    ];
                }
            }
            $params['items'] = $dataItems;

            try {
                $rates = $this->apiHelper->getLiveRates($token, json_encode($params));
                $arrRates = json_decode($rates, true);
                $returnRates = [];
                $i = 0;
                if (isset($arrRates['rates'])) {
                    foreach ($arrRates['rates'] as $value) {
                        $formattedCurrencyValue = $this->priceHelper->currency($value['total_charge'], true, false);
                        $returnRates[$i]['courier_id'] = $value['courier_id'];
                        $returnRates[$i]['courier_name'] = $value['full_description'];
                        $returnRates[$i]['total_charge'] = $value['total_charge'];
                        $returnRates[$i]['total_charge_currency'] = $formattedCurrencyValue;
                        $i++;
                    }
                }
                $data['data'] = $returnRates;
                if(empty($arrRates['rates'])){
                    if (array_key_exists('messages', $arrRates) && !empty($arrRates['messages'])) { // ex : too much QTY
                        $data['error'] = true;
                        $this->messageManager->addError(__($arrRates['messages'][0]));
                    }elseif (array_key_exists('message', $arrRates) && array_key_exists('errors', $arrRates)) { // ex : weight OR dimensional is empty
                        $data['error'] = true;
                        $this->messageManager->addError(__($arrRates['message'].": ".$arrRates['errors'][0]));
                    }elseif (array_key_exists('message', $arrRates)) {
                        $data['error'] = true;
                        $data['message'] = $arrRates['message'];
                        $this->messageManager->addError(__($arrRates['message']));
                    }elseif (array_key_exists('error', $arrRates)){ // ex : bearer token invalid
                        $data['error'] = true;
                        $this->messageManager->addError(__($arrRates['error']));
                    }
                }
            } catch (\Exception $e) {
                $data['error'] = true;
                $data['message'] = $e->getMessage();
            }
        }else{ // ex : bearer token is empty
            $data['error'] = true;
            $this->messageManager->addError(__('No access token found'));
        }
        return $this->jsonFactory->create()->setData($data);
    }
}
