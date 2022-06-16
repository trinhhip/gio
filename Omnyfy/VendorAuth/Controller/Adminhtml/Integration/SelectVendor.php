<?php
namespace Omnyfy\VendorAuth\Controller\Adminhtml\Integration;

class SelectVendor extends \Magento\Integration\Controller\Adminhtml\Integration
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorAuth::system_integrations';

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(__('Select Vendor for Integration'), __('Select Vendor for Integration'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Select Vendor for Integration'));
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Omnyfy_VendorAuth::system_integrations');
    }
}
