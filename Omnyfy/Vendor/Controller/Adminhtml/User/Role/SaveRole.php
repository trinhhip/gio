<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\Vendor\Controller\Adminhtml\User\Role;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Security\Model\SecurityCookie;
use Magento\Framework\Exception\LocalizedException;
use Magento\Authorization\Model\Role as RoleModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveRole extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Session keys for Info form data
     */
    const ROLE_EDIT_FORM_DATA_SESSION_KEY = 'role_edit_form_data';

    /**
     * Session keys for Users form data
     */
    const IN_ROLE_USER_FORM_DATA_SESSION_KEY = 'in_role_user_form_data';

    /**
     * Session keys for original Users form data
     */
    const IN_ROLE_OLD_USER_FORM_DATA_SESSION_KEY = 'in_role_old_user_form_data';

    /**
     * Session keys for Use all resources flag form data
     */
    const RESOURCE_ALL_FORM_DATA_SESSION_KEY = 'resource_all_form_data';

    /**
     * Session keys for Resource form data
     */
    const RESOURCE_FORM_DATA_SESSION_KEY = 'resource_form_data';

    /**
     * @var SecurityCookie
     */
    private $securityCookie;

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
     * Role form submit action to save or create new role
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

        $rid = $this->getRequest()->getParam('role_id', false);
        $resource = $this->getRequest()->getParam('resource', false);

        $oldRoleUsers = $this->parseRequestVariable('in_role_user_old');
        $roleUsers = $this->parseRequestVariable('in_role_user');

        $isSubvendor = $this->getRequest()->getParam('is_subvendor');
        $isAll = $this->getRequest()->getParam('all');
        if ($isAll) {
            $resource = [$this->_objectManager->get(\Magento\Framework\Acl\RootResource::class)->getId()];
        }

        $role = $this->_initRole('role_id');
        if (!$role->getId() && $rid) {
            $this->messageManager->addErrorMessage(__('This role no longer exists.'));
            return $resultRedirect->setPath('adminhtml/*/');
        }

        try {
            $this->validateUser();
            $roleName = $this->_filterManager->removeTags($this->getRequest()->getParam('rolename', false));
            $role->setName($roleName)
                ->setPid($this->getRequest()->getParam('parent_id', false))
                ->setRoleType(RoleGroup::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN)
                ->setIsSubvendor($isSubvendor);

            $this->_eventManager->dispatch(
                'admin_permissions_role_prepare_save',
                ['object' => $role, 'request' => $this->getRequest()]
            );
            $role->save();

            $this->_rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();

            $this->processPreviousUsers($role, $oldRoleUsers);
            $this->processCurrentUsers($role, $roleUsers);
            $this->messageManager->addSuccessMessage(__('You saved the role.'));
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            return $resultRedirect->setPath('*');
        } catch (\Magento\Framework\Exception\AuthenticationException $e) {
            $this->messageManager->addError(__('You have entered an invalid password for current user.'));
            return $this->saveDataToSessionAndRedirect($role, $this->getRequest()->getPostValue(), $resultRedirect);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving this role.'));
        }

        return $resultRedirect->setPath('*/*/');
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
            \Magento\User\Block\Role\Tab\Info::IDENTITY_VERIFICATION_PASSWORD_FIELD
        );
        $user = $this->_authSession->getUser();
        $user->performIdentityCheck($password);

        return $this;
    }

    /**
     * Parse request value from string
     *
     * @param string $paramName
     * @return array
     */
    private function parseRequestVariable(string $paramName): array
    {
        $value = $this->getRequest()->getParam($paramName, null);
        parse_str($value, $value);
        $value = array_keys($value);
        return $value;
    }

    /**
     * @param RoleModel $role
     * @param array $oldRoleUsers
     * @return $this
     * @throws \Exception
     */
    protected function processPreviousUsers(RoleModel $role, array $oldRoleUsers): self
    {
        foreach ($oldRoleUsers as $oUid) {
            $this->_deleteUserFromRole($oUid, $role->getId());
        }

        return $this;
    }

    /**
     * Processes users to be assigned to roles
     *
     * @param RoleModel $role
     * @param array $roleUsers
     * @return $this
     */
    private function processCurrentUsers(RoleModel $role, array $roleUsers): self
    {
        foreach ($roleUsers as $nRuid) {
            try {
                $this->_addUserToRole($nRuid, $role->getId());
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Assign user to role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     * @throws LocalizedException
     */
    protected function _addUserToRole($userId, $roleId)
    {
        $user = $this->_userFactory->create()->load($userId);
        $user->setRoleId($roleId);

        if ($user->roleUserExists() === true) {
            return false;
        } else {
            $user->save();
            return true;
        }
    }

    /**
     * Remove user from role
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     * @throws \Exception
     */
    protected function _deleteUserFromRole($userId, $roleId)
    {
        try {
            $this->_userFactory->create()->setRoleId($roleId)->setUserId($userId)->deleteFromRole();
        } catch (\Exception $e) {
            throw $e;
        }
        return true;
    }

    /**
     * @param RoleModel $role
     * @param array $data
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function saveDataToSessionAndRedirect($role, $data, $resultRedirect)
    {
        $this->_getSession()->setData(self::ROLE_EDIT_FORM_DATA_SESSION_KEY, ['rolename' => $data['rolename']]);
        $this->_getSession()->setData(self::IN_ROLE_USER_FORM_DATA_SESSION_KEY, $data['in_role_user']);
        $this->_getSession()->setData(self::IN_ROLE_OLD_USER_FORM_DATA_SESSION_KEY, $data['in_role_user_old']);
        if ($data['all']) {
            $this->_getSession()->setData(self::RESOURCE_ALL_FORM_DATA_SESSION_KEY, $data['all']);
        } else {
            $resource = isset($data['resource']) ? $data['resource'] : [];
            $this->_getSession()->setData(self::RESOURCE_FORM_DATA_SESSION_KEY, $resource);
        }
        $arguments = $role->getId() ? ['rid' => $role->getId()] : [];
        return $resultRedirect->setPath('*/*/editrole', $arguments);
    }
}
