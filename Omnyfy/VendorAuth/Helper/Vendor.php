<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 23/03/2020
 * Time: 9:28 AM
 */

namespace Omnyfy\VendorAuth\Helper;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Authorization\Model\UserContextInterface;

class Vendor
{
    /**
     * @var \Omnyfy\Vendor\Api\VendorRepositoryInterface
     */
    private $_vendorRepository;

    /**
     * @var \Omnyfy\VendorAuth\Model\Vendor
     */
    private $_vendorAuthVendor;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $_userCollectionFactory;

    /**
     * @var BackendSession
     */
    private $backendSession;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Omnyfy\VendorAuth\Model\Vendor $vendorAuthVendor,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        BackendSession $backendSession,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    )
    {
        $this->_vendorRepository = $vendorRepository;
        $this->_vendorAuthVendor = $vendorAuthVendor;
        $this->_userCollectionFactory = $userCollectionFactory;
        $this->backendSession = $backendSession;
        $this->resourceConnection = $resourceConnection;
    }

    public function getUserId($userName){
        /** @var \Magento\User\Model\ResourceModel\User\Collection $userCollection */
        $userCollection = $this->_userCollectionFactory->create();
        $userCollection->addFieldToFilter('username',['eq' => $userName]);

        if ($userCollection->count() == 1)
            return $userCollection->getFirstItem();
        return null;
    }

    public function getVendorIdFromUserId($userId){
        $vendor = $this->_vendorAuthVendor->getVendorIdByUserId($userId);
        return $vendor;
    }

    public function isMo(){
        $vendorInfo = $this->backendSession->getVendorInfo();
        return empty($vendorInfo);
    }

    public function getResourceIdByRoleId($roleId){
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('authorization_rule');
        $resourceSql = "SELECT `resource_id` FROM " . $tableName .
                    " WHERE `role_id`=". $roleId.
                    " AND `permission` = 'allow'";

        $resourceResult = $connection->fetchCol($resourceSql);
        return $resourceResult;
    }

    public function getVendorParentRoleId($vendorId)
    {
        $connection = $this->resourceConnection->getConnection();
        $roleTable = $connection->getTableName('authorization_role');
        $userTable = $connection->getTableName('omnyfy_vendor_vendor_admin_user');

        $sql = "SELECT `parent_id`
            FROM ".$userTable." ovvau
            JOIN ".$roleTable." ar
            WHERE ovvau.user_id = ar.user_id
            AND ovvau.vendor_id=". $vendorId."
            AND ar.user_type=". UserContextInterface::USER_TYPE_ADMIN;

        $parentRoleId = $connection->fetchOne($sql);
        return $parentRoleId;
    }

}