<?php
namespace Omnyfy\Easyship\Plugin;
use Omnyfy\Vendor\Controller\Adminhtml\Location\Save as LocationSave;

class LocationSavePlugin
{
    protected $scopeConfig;
    protected $accountLocationFactory;
    protected $accountFactory;
    protected $apiHelper;
    protected $regionCollectionFactory;
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $accountLocationFactory,
        \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\Http $response
    ){
        $this->scopeConfig = $scopeConfig;
        $this->accountLocationFactory = $accountLocationFactory;
        $this->accountFactory = $accountFactory;
        $this->apiHelper = $apiHelper;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->messageManager = $messageManager;
        $this->response = $response;
    }

    public function afterExecute(LocationSave $subject, $result){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->scopeConfig->getValue('carriers/easyship/active', $storeScope);

        if ($enable) {
            $data = $subject->getRequest()->getPostValue();

            $regionCode = $data['location']['region'];
            $region = $this->regionCollectionFactory->create()->addRegionNameFilter($regionCode);
            if ($region->getFirstItem()) {
                $regionCode = $region->getFirstItem()->getCode();
            }

            $addressParams['line_1'] = $data['location']['address'];
            $addressParams['postal_code'] = $data['location']['postcode'];
            $addressParams['city'] = $data['location']['suburb'];
            $addressParams['state'] = $regionCode;
            $addressParams['contact_name'] = isset($data['location']['location_contact_name'])? $data['location']['location_contact_name']:"";
            $addressParams['contact_phone'] = isset($data['location']['location_contact_phone'])? $data['location']['location_contact_phone']:"";
            $addressParams['contact_email'] = isset($data['location']['location_contact_email'])? $data['location']['location_contact_email']:"";
            $addressParams['company_name'] = isset($data['location']['location_company_name'])? substr($data['location']['location_company_name'], 0, 27):"";
            $addressParams['name'] = $data['location']['location_name'];

            $vendorLocationId = $subject->getRequest()->getParam('id');
            $model = $this->accountLocationFactory->create();
            $accountLoc = $model->getLocationAccount($vendorLocationId);
            if ($accountLoc) {
                $model->load($accountLoc->getEntityId());

                if (isset($data['easyship_account_id'])) {
                    # There is easyship_account_id param on the post data
                    $accountId = $data['easyship_account_id'];
                    $vendorId = $data['location']['vendor_id'];

                    $account = $this->accountFactory->create()->load($accountId);
                    if ($account->getAccessToken()) {
                        $token = $account->getAccessToken();
                        $addressId = null;

                        if(isset($data['easyship_address_id']) && $data['easyship_address_id'] != ''){
                            $addressId = $data['easyship_address_id'];
                        }

                        try {
                            $address = $this->apiHelper->saveShippingAddress($token, json_encode($addressParams), $addressId);
                            $arrAddress = json_decode($address, true);
                            if (isset($arrAddress['address']) && isset($arrAddress['address']['id'])) {
                                $model->setData('easyship_address_id', $arrAddress['address']['id']);
                            }
                        } catch (\Exception $e) {
                            $this->messageManager->addError(__($e->getMessage()));
                        }

                        $model->setData('vendor_id', $vendorId);
                        $model->setData('vendor_location_id', $vendorLocationId);
                        $model->setData('easyship_account_id', $accountId);
                        $model->save();

                    }else{
                        $this->messageManager->addError(__('Easyship Access Token is not found'));
                    }

                }else{
                    # If vendor didn't open the Shipping Setting tab, easyship_account_id won't be on the post data.
                    # But the location might be changed, update the address on Easyship too (get the token from location_id)
                    $account = $this->accountFactory->create()->load($accountLoc->getEasyshipAccountId());
                    if ($account->getAccessToken()) {
                        $token = $account->getAccessToken();
                        $addressId = null;

                        if ($accountLoc->getEasyshipAddressId() != null) {
                            $addressId = $accountLoc->getEasyshipAddressId();
                        }

                        try {
                            $address = $this->apiHelper->saveShippingAddress($token, json_encode($addressParams), $addressId);
                            $arrAddress = json_decode($address, true);
                            if (isset($arrAddress['address']) && isset($arrAddress['address']['id'])) {
                                $model->setData('easyship_address_id', $arrAddress['address']['id']);
                                $model->save();
                            }
                        } catch (\Exception $e) {
                            $this->messageManager->addError(__($e->getMessage()));
                        }

                    }else{
                        $this->messageManager->addError(__('Easyship Access Token is not found'));
                    }
                }
            }

        }
    }

    public function aroundExecute(LocationSave $subject, callable $process){
        $data = $subject->getRequest()->getParam('location');
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->scopeConfig->getValue('carriers/easyship/active', $storeScope);

        if ($enable) {
            if (isset($data['location_company_name']) && strlen($data['location_company_name']) > 27) {
                $this->messageManager->addErrorMessage(__('Company name is too long. (maximum is 27 characters.)'));
                $id = (int)$subject->getRequest()->getParam('id');
                if (!empty($id)) {
                    $url = $subject->getUrl('omnyfy_vendor/*/edit/id/', ['id' => $id]);
                    $this->response->setRedirect($url);
                } else {
                    $url = $subject->getUrl('omnyfy_vendor/*/new');
                    $this->response->setRedirect($url);
                }
                return $this->response;
            }
        }
        return $process();
    }
}
