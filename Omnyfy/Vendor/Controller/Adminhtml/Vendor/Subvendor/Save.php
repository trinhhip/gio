<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\Vendor\Controller\Adminhtml\Vendor\Subvendor;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Security\Model\SecurityCookie;
use Omnyfy\Vendor\Model\VendorFactory;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Math\Random;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\User\Controller\Adminhtml\User
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
     * @var SecurityCookie
     */
    private $securityCookie;

    protected $vendorFactory;

    protected $transportBuilder;

    protected $dataHelper;

    protected $storeManager;

    protected $backendHelper;

    protected $random;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory,
        VendorFactory $vendorFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Omnyfy\Vendor\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        BackendHelper $backendHelper,
        Random $random
    ) {
        parent::__construct($context, $coreRegistry, $userFactory);
        $this->_coreRegistry = $coreRegistry;
        $this->_userFactory = $userFactory;
        $this->vendorFactory = $vendorFactory;
        $this->transportBuilder = $transportBuilder;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->backendHelper = $backendHelper;
        $this->random = $random;
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

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        if (array_key_exists('form_key', $data)) {
            unset($data['form_key']);
        }
        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        /** @var $model \Magento\User\Model\User */
        $model = $this->_userFactory->create()->load($userId);
        if ($userId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This user no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }
        $model->setData($this->_getAdminUserData($data));
        $userRoles = $this->getRequest()->getParam('roles', []);
        if (count($userRoles)) {
            $model->setRoleId($userRoles[0]);
        }

        /** @var $currentUser \Magento\User\Model\User */
        $currentUser = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser();
        if ($userId == $currentUser->getId()
            && $this->_objectManager->get(\Magento\Framework\Validator\Locale::class)
                ->isValid($data['interface_locale'])
        ) {
            $this->_objectManager->get(
                \Magento\Backend\Model\Locale\Manager::class
            )->switchBackendInterfaceLocale(
                $data['interface_locale']
            );
        }

        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        $currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
        $isCurrentUserPasswordValid = isset($data[$currentUserPasswordField])
            && !empty($data[$currentUserPasswordField]) && is_string($data[$currentUserPasswordField]);
        try {
            if (!($isCurrentUserPasswordValid)) {
                throw new AuthenticationException(__('You have entered an invalid password for current user.'));
            }
            $currentUser->performIdentityCheck($data[$currentUserPasswordField]);
            $model->setIsSubvendor(1);

            $parentVendorId = $this->getRequest()->getParam('parent_vendor_id');

            if ($parentVendorId == 0) {
                $model->setParentVendorId(0);
            } else {
                $model->setParentVendorId($parentVendorId);
            }

            $newPassResetToken = $this->backendHelper->generateResetPasswordLinkToken();
            if ($model->isObjectNew()) {
                $password = $this->random(Random::CHARS_LOWERS)
                    . $this->random(Random::CHARS_DIGITS)
                    . $this->random(Random::CHARS_UPPERS);
                $model->setPassword($password);
                $model->changeResetPasswordLinkToken($newPassResetToken);
            }

            $model->save();

            $this->messageManager->addSuccess(__('You saved the user.'));
            $this->_getSession()->setUserData(false);
            $this->_redirect('omnyfy_vendor/*/');

            $model->sendNotificationEmailsIfRequired();
            $this->sendNotifyToSubVendor($parentVendorId, $model);
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            $this->_redirect('omnyfy_vendor/*/');
        } catch (MailException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError(__('You have entered an invalid password for current user.'));
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    protected function redirectToEdit(\Magento\User\Model\User $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['user_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('omnyfy_vendor/*/edit', $arguments);
    }

    protected function sendNotifyToSubVendor($parentVendorId, $subVendorUser)
    {
        if ($subVendorUser->isObjectNew()) {
            $parentVendor = $this->vendorFactory->create()->load($parentVendorId);
            $templateId = $this->dataHelper->getConfigValue(
                'omnyfy_vendor_subvendor/notification/template'
            );
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars(
                    [
                        'sub_vendor' => [
                            'firstname' => $subVendorUser->getFirstName(),
                            'username' => $subVendorUser->getUserName()
                        ],
                        'vendor' => [
                            'name' => $parentVendor->getResource()->getVendorRegistrationName($parentVendor->getId()),
                            'company' => $parentVendor->getName()
                        ],
                        'user' => $subVendorUser
                    ]
                )
                ->setFrom(
                    $this->dataHelper->getConfigValue('admin/emails/forgot_email_identity')
                )
                ->addTo($subVendorUser->getEmail(), $subVendorUser->getName())
                ->getTransport();
            $transport->sendMessage();
        }
    }

    protected function random($chars) {
        return $this->random->getRandomString(10, $chars);
    }
}
