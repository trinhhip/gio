<?php

namespace Omnyfy\RebateUI\Controller\Adminhtml\Table;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Omnyfy\RebateUI\Controller\Adminhtml\Table
 */
class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Omnyfy_RebateUI::rebate_report';
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
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Omnyfy_RebateUI::rebate_report')
            ->addBreadcrumb(__('Rebates Report By Vendor'), __('Rebates Report By Vendor'))->getConfig()->getTitle()->prepend(__('Rebates Report By Vendor'));

        return $resultPage;
    }
}