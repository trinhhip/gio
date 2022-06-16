<?php


namespace OmnyfyCustomzation\B2C\Controller\Adminhtml\Account;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'OmnyfyCustomzation_ShippingCalculatedWeight::vermillion';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->_setActiveMenu('OmnyfyCustomzation_Core::vermillion');
        $resultPage->getConfig()->getTitle()->prepend(__('Request Become To Trader'));
        $resultPage->addBreadcrumb(__('Request Become To Trader'), __('Request Become To Trader'));
        $resultPage->addBreadcrumb(__('Request Become To Trader'), __('Request Become To Trader'));
        return $resultPage;
    }
}
