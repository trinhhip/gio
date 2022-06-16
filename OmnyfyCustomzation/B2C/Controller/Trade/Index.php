<?php


namespace OmnyfyCustomzation\B2C\Controller\Trade;


use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\B2C\Helper\Data;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;

class Index extends Action
{

    protected $resultPageFactory;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var Registration
     */
    protected $registration;
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param Registration $registration
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        Registration $registration,
        Data $helperData
    )
    {
        $this->session = $customerSession;
        $this->registration = $registration;
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $customer = $this->session->getCustomer();

            switch ($customer->getIsApproved()) {
                case AttributeOptions::RETAIL_TO_TRADE :
                    $message = __('You have submitted a request to upgrade to a trade account. Please wait until approval.');
                    $this->messageManager->addWarningMessage($message);
                    $resultRedirect->setPath('customer/account');
                    break;
                case AttributeOptions::APPROVED:
                    $message = __('Your account is currently a trading account.');
                    $this->messageManager->addSuccessMessage($message);
                    $resultRedirect->setPath('customer/account');
                    break;
                default:
                    $resultRedirect->setPath('customer/account');
                    break;
            }

            if ($customer->getGroupId() == $this->helperData->getDefaultCustomerGroup() && $customer->getIsApproved() != AttributeOptions::RETAIL_TO_TRADE) {
                $resultRedirect->setPath('buyer/trade/create', ['email' => $customer->getEmail()]);
            }

            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
