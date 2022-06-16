<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\VendorAuth\Block\Adminhtml\Integration\Vendor;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Helper\Data;
use Omnyfy\VendorAuth\Helper\Vendor;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Framework\App\ResourceConnection;
use \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Omnyfy\Vendor\Model\VendorFactory;

class OmnyfyWebapi extends \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Webapi
{


    /**
     * @var Vendor
     */
    private $vendorHelper;

    private $request;

    private $adminSession;

    private $roleCollectionFactory;

    private $vendorFactory;

    public function __construct(
       Context $context,
       Registry $registry,
       FormFactory $formFactory,
       RootResource $rootResource,
       ProviderInterface $aclResourceProvider,
       Data $integrationData,
       IntegrationServiceInterface $integrationService,
       Vendor $vendorHelper,
       AdminSession $adminSession,
       RoleCollectionFactory $roleCollectionFactory,
       VendorFactory $vendorFactory,
       array $data = []
   ) {
       parent::__construct($context, $registry, $formFactory, $rootResource, $aclResourceProvider, $integrationData,
           $integrationService, $data);
        $this->vendorHelper = $vendorHelper;
        $this->request = $context->getRequest();
        $this->adminSession = $adminSession;
        $this->roleCollectionFactory = $roleCollectionFactory;
        $this->vendorFactory = $vendorFactory;
    }

    public function isMo(){
        return $this->vendorHelper->isMo();
    }

    public function isEditAction() {
        $action = $this->request->getFullActionName();
        if ($action == "omnyfy_vendorauth_integration_edit") {
            return true;
        }
        return false;
    }

    public function getSelectedResources(){

        $defaultResource = parent::getSelectedResources();

        if (count($defaultResource) > 0) {
            return $defaultResource;
        }

        $roleData = $this->adminSession->getUser()->getRole()->getData();
        $roleId = $this->getRoleId($roleData['role_id']);
        $resourceResult = $this->vendorHelper->getResourceIdByRoleId($roleId);
        return $resourceResult;
    }

    protected function getRoleId($roleId){
        if ($this->isMo()) {
            $role = null;

            if ($this->isEditAction()) {
                $integrationId = $this->request->getParam('id');
                $role = $this->_getUserRole(true, $integrationId);
            }else{
                $vendorId = $this->request->getParam('vendor_id');
                $role = $this->_getUserRole(false, $vendorId);
            }

            if ($role) {
                $roleId = $role->getId();
            }
        }
        return $roleId;
    }

    protected function _getUserRole($isIntegration, $id=null){
        $roleCollection = $this->roleCollectionFactory->create();
        $role = null;

        if ($isIntegration) {
            $role = $roleCollection
                ->setUserFilter($id, UserContextInterface::USER_TYPE_INTEGRATION)
                ->getFirstItem();
        }else{
            $vendorParentRoleId = $this->vendorHelper->getVendorParentRoleId($id);
            $role = $roleCollection
                ->addFieldToFilter('role_id', $vendorParentRoleId)
                ->addFieldToFilter('user_type', UserContextInterface::USER_TYPE_ADMIN)
                ->getFirstItem();
        }
        return $role->getId() ? $role : false;
    }

    public function getVendorName(){
        $vendorId = $this->getRequest()->getParam('vendor_id');
        $vendor = $this->vendorFactory->create()->load($vendorId);
        return $vendor->getName();
    }
}