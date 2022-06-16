<?php
/**
 *
 * Copyright © Omnyfy, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorAuth\Controller\Adminhtml\Integration;

class Index extends \Magento\Integration\Controller\Adminhtml\Integration
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorAuth::system_integrations';

    /**
     * Integrations grid.
     *
     * @return void
     */
    public function execute()
    {
        $unsecureIntegrationsCount = $this->_integrationCollection->addUnsecureUrlsFilter()->getSize();
        if ($unsecureIntegrationsCount > 0) {
            // @codingStandardsIgnoreStart
            $this->messageManager->addNotice(__('Warning! Integrations not using HTTPS are insecure and potentially expose private or personally identifiable information')
            // @codingStandardsIgnoreEnd
            );
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Omnyfy_VendorAuth::system_integrations');
        $this->_addBreadcrumb(__('Vendor\'s Integrations'), __('Vendor\'s Integrations'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Vendor\'s Integrations'));
        $this->_view->renderLayout();
    }
}
