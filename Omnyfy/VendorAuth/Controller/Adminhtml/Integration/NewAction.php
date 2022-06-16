<?php
/**
 *
 * Copyright © Omnyfy, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorAuth\Controller\Adminhtml\Integration;

class NewAction extends \Magento\Integration\Controller\Adminhtml\Integration
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorAuth::system_integrations';

    protected $vendorFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        \Magento\Integration\Api\OauthServiceInterface $oauthService,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Integration\Helper\Data $integrationData,
        \Magento\Framework\Escaper $escaper,
        \Magento\Integration\Model\ResourceModel\Integration\Collection $integrationCollection,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory
    ) {
        parent::__construct($context, $registry, $logger, $integrationService, $oauthService, $jsonHelper, $integrationData, $escaper, $integrationCollection);
        $this->vendorFactory = $vendorFactory;
    }

    /**
     * New integration action.
     *
     * @return void
     */
    public function execute()
    {
        $this->restoreResourceAndSaveToRegistry();
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(__('New Vendor Integration'), __('New Vendor Integration'));
        $title = $this->getIntegrationTitle();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__($title));
        /** Try to recover integration data from session if it was added during previous request which failed. */
        $restoredIntegration = $this->_getSession()->getIntegrationData();
        if ($restoredIntegration) {
            $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $restoredIntegration);
            $this->_getSession()->setIntegrationData([]);
        }
        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Omnyfy_VendorAuth::system_integrations');
    }

    public function getIntegrationTitle(){
        $vendorId = $this->getRequest()->getParam('vendor_id');
        $vendor = $this->vendorFactory->create()->load($vendorId);
        return "Create ".$vendor->getName()." Integration";
    }
}
