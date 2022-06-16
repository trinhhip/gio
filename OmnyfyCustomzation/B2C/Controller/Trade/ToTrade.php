<?php


namespace OmnyfyCustomzation\B2C\Controller\Trade;


use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\B2C\Helper\BuyerAccount as BuyerAccountHelper;
use OmnyfyCustomzation\B2C\Helper\Data;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;

class ToTrade extends Action
{

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var Data
     */
    public $helperData;
    /**
     * @var BuyerAccountHelper
     */
    public $buyerAccountHelper;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        Data $helperData,
        BuyerAccountHelper $buyerAccountHelper
    )
    {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->helperData = $helperData;
        $this->buyerAccountHelper = $buyerAccountHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = $this->getRequest()->getParam('email');
        if (!$email) {
            $this->messageManager->addWarningMessage(__('Please enter your email!'));
            $resultRedirect->setPath('buyer/trade/index');
            return $resultRedirect;
        }
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
        if ($customer->getId()) {
            $loginUrl = $this->_url->getUrl('customer/account/login');
            $this->messageManager->addComplexNoticeMessage(
                'buyerToTrade',
                [
                    'loginUrl' => $loginUrl,
                    'email' => $email
                ]
            );
            $resultRedirect->setPath('buyer/trade/index');
            return $resultRedirect;
        }
        $this->buyerAccountHelper->requestToTrade($email, AttributeOptions::UNREGISTERED);
        $resultRedirect->setPath('customer/account/create', ['email' => $email]);
        return $resultRedirect;
    }
}
