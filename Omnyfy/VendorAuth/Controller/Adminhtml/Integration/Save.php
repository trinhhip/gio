<?php
/**
 * Copyright © Omnyfy, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorAuth\Controller\Adminhtml\Integration;

use Magento\Framework\Exception\NotFoundException;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Security\Model\SecurityCookie;

/**
 * Integration Save controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Integration\Controller\Adminhtml\Integration
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorAuth::system_integrations';

    /**
     * @var SecurityCookie
     */
    private $securityCookie;

    private $redirect;

    private $vendorHelper;

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
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Omnyfy\VendorAuth\Helper\Vendor $vendorHelper
    ) {
        parent::__construct($context, $registry, $logger, $integrationService, $oauthService, $jsonHelper, $integrationData, $escaper, $integrationCollection);
        $this->redirect = $redirect;
        $this->vendorHelper = $vendorHelper;
    }

    /**
     * Get security cookie
     *
     * @return SecurityCookie
     * @deprecated 100.1.0
     */
    private function getSecurityCookie()
    {
        if (!($this->securityCookie instanceof SecurityCookie)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(SecurityCookie::class);
        } else {
            return $this->securityCookie;
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Omnyfy_VendorAuth::system_integrations');
    }

    /**
     * Save integration action.
     *
     * @return void
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        /** @var array $integrationData */
        $integrationData = [];
        try {
            $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
            if ($integrationId) {
                $integrationData = $this->getIntegration($integrationId);
                if (!$integrationData) {
                    return;
                }
                if ($integrationData[Info::DATA_SETUP_TYPE] == IntegrationModel::TYPE_CONFIG) {
                    throw new LocalizedException(__('Cannot edit Vendor integrations created via config file.'));
                }
            }
            $this->validateUser();
            $this->processData($integrationData);
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            $this->_redirect('*');
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_getSession()->setIntegrationData($this->getRequest()->getPostValue());
            $this->_redirectOnSaveError();
        } catch (IntegrationException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_getSession()->setIntegrationData($integrationData);
            $this->_redirectOnSaveError();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirectOnSaveError();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirectOnSaveError();
        }
    }

    /**
     * Validate current user password
     *
     * @return $this
     * @throws UserLockedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    protected function validateUser()
    {
        $password = $this->getRequest()->getParam(
            \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info::DATA_CONSUMER_PASSWORD
        );
        $user = $this->_auth->getUser();
        $user->performIdentityCheck($password);

        return $this;
    }

    /**
     * Get Integration entity
     *
     * @param int $integrationId
     * @return \Magento\Integration\Model\Integration|null
     */
    protected function getIntegration($integrationId)
    {
        try {
            $integrationData = $this->_integrationService->get($integrationId)->getData();
        } catch (IntegrationException $e) {
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            $this->_redirect('*/*/');
            return null;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->messageManager->addError(__('Internal error. Check exception log for details.'));
            $this->_redirect('*/*');
            return null;
        }

        return $integrationData;
    }

    /**
     * Redirect merchant to 'Edit integration' or 'New integration' if error happened during integration save.
     *
     * @return void
     */
    protected function _redirectOnSaveError()
    {
        $integrationId = $this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            $this->_redirect('*/*/edit', ['id' => $integrationId]);
        } else {
            $this->_redirect('*/*/new', ['vendor_id' => $this->getRequest()->getParam('vendor_id')]);
        }
    }

    /**
     * Save integration data.
     *
     * @param array $integrationData
     * @return void
     */
    private function processData($integrationData)
    {
        /** @var array $data */
        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            if (!isset($data['resource'])) {
                $integrationData['resource'] = [];
            }

            $refererUrl = $this->redirect->getRefererUrl();
            if ((!strpos($refererUrl, 'omnyfy_vendorauth/integration/edit') && !$this->vendorHelper->isMo()) || $this->vendorHelper->isMo() || !strpos($refererUrl, 'omnyfy_vendorauth/integration/edit')) {
                $integrationData = array_merge($integrationData, $data);
            }

            if (!isset($integrationData[Info::DATA_ID])) {
                $integration = $this->_integrationService->create($integrationData);
            } else {
                $integration = $this->_integrationService->update($integrationData);
            }
            if (!$this->getRequest()->isXmlHttpRequest()) {
                $this->messageManager->addSuccess(
                    __(
                        'The Vendor integration \'%1\' has been saved.',
                        $this->escaper->escapeHtml($integration->getName())
                    )
                );
                $this->_redirect('*/*/');
            } else {
                $isTokenExchange = $integration->getEndpoint() && $integration->getIdentityLinkUrl() ? '1' : '0';
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode(
                        ['integrationId' => $integration->getId(), 'isTokenExchange' => $isTokenExchange]
                    )
                );
            }
        } else {
            $this->messageManager->addError(__('The Vendor integration was not saved.'));
        }
    }
}
