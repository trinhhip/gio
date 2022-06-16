<?php
/**
 * Copyright © 2017 Omnyfy. All rights reserved.
 */

namespace Omnyfy\Vendor\Controller\Adminhtml\Vendor\Subvendor;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Omnyfy\Vendor\Model\VendorFactory;
use Psr\Log\LoggerInterface;

class Edit extends \Magento\User\Controller\Adminhtml\User
{
    const ADMIN_RESOURCE = 'Omnyfy_Vendor::vendor_subvendor';
    protected $resourceKey = 'Omnyfy_Vendor::vendor_subvendor';

    protected $adminTitle = 'Vendor Subvendors';


    public function execute()
    {
        $userId = $this->getRequest()->getParam('id');
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        if ($userId) {
            $model->load($userId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This user no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        } else {
            $model->setInterfaceLocale(Resolver::DEFAULT_LOCALE);
        }

        // Restore previously entered form data from session
        $data = $this->_session->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }


        $this->_coreRegistry->register('permissions_user', $model);

        if (isset($userId)) {
            $breadcrumb = __('Edit Subvendor');
        } else {
            $breadcrumb = __('New Subvendor');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Users'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Subvendor'));
        $this->_view->renderLayout();
    }
}
