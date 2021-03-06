<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorAuth\Controller\Adminhtml\Integration;

use Magento\Framework\Exception\NotFoundException;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Integration\Controller\Adminhtml\Integration
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorAuth::system_integrations';

    /**
     * Delete the integration.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        try {
            if ($integrationId) {
                $integrationData = $this->_integrationService->get($integrationId);
                if ($this->_integrationData->isConfigType($integrationData)) {
                    $this->messageManager->addError(
                        __(
                            "Uninstall the extension to remove integration '%1'.",
                            $this->escaper->escapeHtml($integrationData[Info::DATA_NAME])
                        )
                    );
                    return $resultRedirect->setPath('*/*/');
                }
                $integrationData = $this->_integrationService->delete($integrationId);
                if (!$integrationData[Info::DATA_ID]) {
                    $this->messageManager->addError(__('This integration no longer exists.'));
                } else {
                    //Integration deleted successfully, now safe to delete the associated consumer data
                    if (isset($integrationData[Info::DATA_CONSUMER_ID])) {
                        $this->_oauthService->deleteConsumer($integrationData[Info::DATA_CONSUMER_ID]);
                    }
                    $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);
                    $this->messageManager->addSuccess(
                        __(
                            "The integration '%1' has been deleted.",
                            $this->escaper->escapeHtml($integrationData[Info::DATA_NAME])
                        )
                    );
                }
            } else {
                $this->messageManager->addError(__('Integration ID is not specified or is invalid.'));
            }
        } catch (IntegrationException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Omnyfy_VendorAuth::system_integrations');
    }
}
