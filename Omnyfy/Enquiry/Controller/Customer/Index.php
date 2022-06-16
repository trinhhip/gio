<?php
namespace Omnyfy\Enquiry\Controller\Customer;

use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{

	/**
     * @var PageFactory
     */
    protected $resultPageFactory;
	
	/**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
		$this->_customerSession = $customerSession;
        parent::__construct($context);
    }

	public function execute() {
		
		if (!$this->_customerSession->isLoggedIn()) {
            $this->_redirect('customer/account');
            return;
        }
		
		$this->_view->loadLayout();
		$this->_view->getLayout()->initMessages();
		$resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set('My Enquires');
		$listBlock = $this->_view->getLayout()->getBlock('enquires');

		if ($listBlock) {
			$currentPage = abs(intval($this->getRequest()->getParam('p')));
			if ($currentPage < 1) {
				$currentPage = 1;
			}
			
			$listBlock->setCurrentPage($currentPage);
		}
		
		return $resultPage;
	}

}