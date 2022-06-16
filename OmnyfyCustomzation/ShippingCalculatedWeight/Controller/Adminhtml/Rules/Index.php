<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\ShippingCalculatedWeight\Controller\Adminhtml\CalculatedWeight;

class Index extends CalculatedWeight
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->_setActiveMenu('OmnyfyCustomzation_ShippingCalculatedWeight::vermillion');
        $resultPage->getConfig()->getTitle()->prepend(__('Calculation Rules'));
        $resultPage->addBreadcrumb(__('Shipping Weight'), __('Shipping Weight'));
        $resultPage->addBreadcrumb(__('Calculation Rules'), __('Calculation Rules'));
        return $resultPage;
    }
}

