<?php


namespace OmnyfyCustomzation\B2C\Controller\Trade;


use Exception;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\B2C\Helper\Data as HelperData;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use OmnyfyCustomzation\B2C\Helper\BuyerAccount as BuyerAccountHelper;

class UpdateTrade extends Action
{
    const MESSAGE_AFTER_REGISTER = 'buyerapproval/general/message_after_register';

    /**
     * @var Customer
     */
    public $customer;
    /**
     * @var CustomerFactory
     */
    public $customerFactory;
    /**
     * @var Validator
     */
    public $formKeyValidator;
    /**
     * @var Session
     */
    public $customerSession;
    /**
     * @var CustomerRepositoryInterface
     */
    public $customerRepository;
    /**
     * @var HelperData
     */
    public $helperData;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var BuyerAccountHelper
     */
    public $buyerAccountHelper;

    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        Validator $formKeyValidator,
        Session $customerSession,
        HelperData $helperData,
        BuyerAccountHelper $buyerAccountHelper,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->buyerAccountHelper = $buyerAccountHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('*/*/*');
            return $resultRedirect;
        }

        try {
            $customer = $this->customerSession->getCustomer();
            if (!$customer) {
                $this->messageManager->addWarningMessage(__('Can\'t make request now, please try again later.'));
                $resultRedirect->setPath('buyer/trade/index');
            } else {
                $this->buyerAccountHelper->requestToTrade($customer->getEmail(), AttributeOptions::RETAIL_TO_TRADE);
                $customer->setIsApproved(AttributeOptions::RETAIL_TO_TRADE);
                $customerData = $customer->getDataModel();

                foreach ($this->getTradeFiled() as $attribute) {
                    $customerData->setCustomAttribute($attribute, $this->getRequest()->getParam($attribute));
                }
                $customer->updateData($customerData);
                $this->customerRepository->save($customerData);
                $messageAfterRegister = $this->scopeConfig->getValue(self::MESSAGE_AFTER_REGISTER, ScopeInterface::SCOPE_STORE);
                $this->messageManager->addComplexSuccessMessage(
                    'addAfterSignupMessage',
                    [
                        "message" => $messageAfterRegister
                    ]
                );
                $resultRedirect->setPath('customer/account');
                return $resultRedirect;
            }
        } catch (Exception $e) {
            $writer = new Stream(BP . '/pub/media/b2c.log');
            $logger = new Logger();
            $logger->addWriter($writer);
            $logger->info($e->getMessage());
            $this->messageManager->addWarningMessage(__('There was an error creating the request'));
            $resultRedirect->setPath('buyer/trade/index');
        }

        return $resultRedirect;
    }

    public function getTradeFiled()
    {
        return [
            'business_name',
            'business_url',
            'designation',
            'business_type',
            'business_location'
        ];
    }

}
