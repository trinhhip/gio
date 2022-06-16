<?php


namespace OmnyfyCustomzation\B2C\Controller\Retail;


use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use OmnyfyCustomzation\B2C\Helper\Data as HelperData;


class CreateAjax extends Action
{
    const HTTP_BAD_REQUEST_CODE = 400;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;
    /**
     * @var RawFactory
     */
    public $resultRawFactory;
    /**
     * @var JsonHelper
     */
    public $jsonHelper;
    /**
     * @var HelperData
     */
    public $helperData;
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;
    /**
     * @var CustomerExtractor
     */
    public $customerExtractor;
    /**
     * @var AccountManagementInterface
     */
    public $accountManagement;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    public $customerFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    private $formFactory;
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;
    private $cookieMetadataManager;
    private $cookieMetadataFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        RawFactory $resultRawFactory,
        JsonHelper $jsonHelper,
        HelperData $helperData,
        \Magento\Customer\Model\Session $customerSession,
        CustomerExtractor $customerExtractor,
        AccountManagementInterface $accountManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerInterfaceFactory
    )
    {
        $this->resultJsonFactory = $jsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->customerExtractor = $customerExtractor;
        $this->accountManagement = $accountManagement;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        parent::__construct($context);
    }

    protected function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(
                PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    protected function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(
                CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('');
            return $resultRedirect;
        }
        $resultJson = $this->resultJsonFactory->create();
        $response = [
            'success' => false,
            'message' => __('Register successfully.')
        ];
        if ($this->customerSession->isLoggedIn()) {
            $response['message'] = __('You are already logged in.');
            return $resultJson->setData($response);
        }

        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());
        } catch (Exception $e) {
            return $resultRaw->setHttpResponseCode(self::HTTP_BAD_REQUEST_CODE);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode(self::HTTP_BAD_REQUEST_CODE);
        }

        if ($this->checkPasswordConfirmation($credentials['password'], $credentials['password_confirmation'])) {
            $response['message'] = __('Please make sure your passwords match.');
            return $resultJson->setData($response);
        }
        $password = $credentials['password'];
        $customer = $this->prepareCustomer($credentials);

        try {
            $this->customerSession->regenerateId();
            $customer = $this->accountManagement->createAccount($customer, $password);
            $this->_eventManager->dispatch(
                'buyer_register_success',
                ['register_controller' => $this, 'customer' => $customer]
            );
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }
        } catch (StateException $e) {
            $response['message'] = __('An account with email %1 already exists.', $credentials['email']);
            return $resultJson->setData($response);
        } catch (LocalizedException $e) {
            $response['message'] = $e->getMessage();
            return $resultJson->setData($response);
        }
        $response = [
            'success' => true,
            'message' => __('Register successfully.')
        ];
        return $resultJson->setData($response);
    }

    protected function checkPasswordConfirmation($password, $confirmation)
    {
        return $password != $confirmation;
    }

    public function prepareCustomer($customerData)
    {
        $store = $this->storeManager->getStore();
        $customer = $this->customerInterfaceFactory->create();
        foreach ($this->getRetailAttributes() as $attribute) {
            $attributeData = isset($customerData[$attribute]) ? $customerData[$attribute] : '';
            $customer->setData($attribute, $attributeData);
        }
        $customer->setGroupId($this->helperData->getDefaultCustomerGroup());
        $customer->setWebsiteId($store->getWebsiteId());
        $customer->setStoreId($store->getId());
        return $customer;
    }

    public function getRetailAttributes()
    {
        return [
            'firstname',
            'lastname',
            'email',
            'country_code',
            'phone_number'
        ];
    }
}
