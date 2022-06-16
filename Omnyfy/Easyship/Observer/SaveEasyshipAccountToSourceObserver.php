<?php

namespace Omnyfy\Easyship\Observer;

class SaveEasyshipAccountToSourceObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected $regionCollectionFactory;
    protected $scopeConfig;
    protected $accountFactory;
    protected $apiHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->accountFactory = $accountFactory;
        $this->apiHelper = $apiHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->scopeConfig->getValue('carriers/easyship/active', $storeScope);

        if ($enable) {
            $requestData = $observer->getRequest()->getPostValue();
            $data = $requestData['general'];
            $regionCode = '';
            if (isset($data['region_id'])) {
                $region = $this->regionCollectionFactory->create()->getItemById($data['region_id']);
                if ($region != null) {
                    $regionCode = $region->getCode();
                }
            }
            $addressParams['postal_code'] = isset($data['postcode']) ? $data['postcode'] : '';
            $addressParams['line_1'] = isset($data['street']) ? $data['street'] : '';
            $addressParams['city'] = isset($data['city']) ? $data['city'] : $data['city'];
            $addressParams['state'] = $regionCode;
            $addressParams['contact_name'] = isset($data['contact_name']) ? $data['contact_name'] : '';
            $addressParams['contact_phone'] = isset($data['phone']) ? $data['phone'] : '';
            $addressParams['contact_email'] = isset($data['email']) ? $data['email'] : '';
            $addressParams['company_name'] = isset($data['company_name']) ? $data['company_name'] : 'test';
            $addressParams['name'] = isset($data['name']) ? $data['name'] : '';

            $inventorySource = $observer->getSource();

            if (isset($data['easyship_account_id']) && $data['easyship_account_id'] != null) {
                $inventorySource->setEasyshipAccountId($data['easyship_account_id']);
            }

            $inventorySource->setCompanyName($data['company_name']);
            $inventorySource->save();
            if (isset($data['easyship_account_id']) && $data['easyship_account_id'] != null) {
                $accountId = $data['easyship_account_id'];
                $account = $this->accountFactory->create()->load($accountId);
                if ($account->getAccessToken()) {
                    $token = $account->getAccessToken();
                    $addressId = null;

                    if (isset($data['easyship_address_id']) && $data['easyship_address_id'] != '') {
                        $addressId = $data['easyship_address_id'];
                    }

                    try {
                        $address = $this->apiHelper->saveShippingAddress($token, json_encode($addressParams), $addressId);
                        $arrAddress = json_decode($address, true);
                        if (isset($arrAddress['address']) && isset($arrAddress['address']['id'])) {
                            $inventorySource->setData('easyship_address_id', $arrAddress['address']['id']);
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__($e->getMessage()));
                    }

                    $inventorySource->setData('easyship_account_id', $accountId);
                    $inventorySource->save();
                } else {
                    $this->messageManager->addError(__('Easyship Access Token is not found'));
                }
            }
        }
    }
}
