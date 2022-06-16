<?php


namespace OmnyfyCustomzation\B2C\Controller\Trade;


use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\B2C\Helper\Data;

class Create extends Action
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
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->session->isLoggedIn()) {
            $customer = $this->session->getCustomer();
            if ($customer->getGroupId() == $this->helperData->getTradeCustomerGroup()){
                $resultRedirect->setPath('buyer/trade/index');
                return $resultRedirect;
            }
        } else if (!$this->getRequest()->getParam('email')) {
            $this->messageManager->addWarningMessage(__('Please enter your email.'));
            $resultRedirect->setPath('buyer/trade/index');
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }
}
