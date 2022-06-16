<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\BookPickup;

class View extends \Magento\Backend\App\Action
{
    /**
     * acl
     */
    const ADMIN_RESOURCE = 'Omnyfy_Easyship::bookpickup';

    protected $resultPageFactory;

    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales');
        $resultPage->getConfig()->getTitle()->prepend(__('Easyship Booking Pickup Detail'));
        return $resultPage;
    }

    protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Omnyfy_Easyship::bookpickup');
	}
}
