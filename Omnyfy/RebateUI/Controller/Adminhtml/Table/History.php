<?php

namespace Omnyfy\RebateUI\Controller\Adminhtml\Table;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Omnyfy\Vendor\Model\VendorFactory;

/**
 * Class History
 * @package Omnyfy\RebateUI\Controller\Adminhtml\Table
 */
class History extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Omnyfy\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        VendorFactory $vendorFactory,
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    /**
     * History action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $vendorId = $this->getRequest()->getParam('vendor_id');
        if (!$vendorId) {
            $vendorInfo = $this->_session->getVendorInfo();
            if (!empty($vendorInfo)) {
                $vendorId = $vendorInfo['vendor_id'];
            }
        }
        if ($vendorId) {
            $vendorName = '';
            $model = $this->vendorFactory->create()->load($vendorId);
            $vendorName = $model->getName();
            $this->_view->getPage()->getConfig()->getTitle()->prepend('Invoice History for ' . $vendorName);
        }
        $resultPage->addBreadcrumb(__('Invoice History for '  . $vendorName), __('Invoice History for '  . $vendorName));
        
        return $resultPage;
    }
}