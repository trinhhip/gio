<?php

namespace Omnyfy\Vendor\Controller\Adminhtml\Vendor\Subvendor;

use Omnyfy\Vendor\Controller\Adminhtml\AbstractAction;

/**
 * Class Index
 * @package Omnyfy\Vendor\Controller\Adminhtml\Vendor\Subvendor
 */
class Index extends AbstractAction
{
    /**
     *
     */
    const ADMIN_RESOURCE = 'Omnyfy_Vendor::vendor_subvendor';
    /**
     * @var string
     */
    protected $resourceKey = 'Omnyfy_Vendor::vendor_subvendor';

    /**
     * @var string
     */
    protected $adminTitle = 'Vendor Subvendors';

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Vendor Subvendors'));
        $resultPage->addBreadcrumb(__('Omnyfy'), __('Omnyfy'));
        $resultPage->addBreadcrumb(__('Vendor Subvendors'), __('Vendor Subvendors'));

        return $resultPage;
    }
}
