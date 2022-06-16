<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Vendor\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

class InstallDefaultData implements DataPatchInterface
{

    protected $moduleDataSetup;
    protected $roleFactory;
    protected $rulesFactory;
    protected $omnyfyHelper;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory,
        \Omnyfy\Vendor\Helper\Data $omnyfyHelper
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->roleFactory = $roleFactory;
        $this->rulesFactory = $rulesFactory;
        $this->omnyfyHelper = $omnyfyHelper;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $setup = $this->moduleDataSetup;
        $conn = $this->moduleDataSetup->getConnection();
        $eavAttrTable = $conn->getTableName('eav_attribute');
        $vendorEntityTypeId = $this->getEntityTypeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, $conn);

         //check if vendor admin role exists, create it if not there
         $vendorAdminRoleName = 'Vendor Admin';
         if (!$this->isRoleExists($setup, $vendorAdminRoleName))
         {
             $role = $this->roleFactory->create();
             $role->setName($vendorAdminRoleName)
                 ->setPid(0)
                 ->setRoleType(RoleGroup::ROLE_TYPE)
                 ->setUserType(UserContextInterface::USER_TYPE_ADMIN)
                 ;
             $role->save();
             $resource = [
                 'Magento_Backend::admin',
                 'Magento_Sales::sales',
                 'Magento_Sales::actions_view',
                 'Magento_Sales::sales_invoice',
                 'Magento_Sales::shipment',
             ];
             $this->rulesFactory->create()
                 ->setRoleId($role->getId())
                 ->setResources($resource)
                 ->saveRel();
         }


        //Add default vendor type.
        $tableName = $conn->getTableName('omnyfy_vendor_vendor_type');
        $vendorDefaultAttributeSetId = $this->getEntityDefaultAttributeSetId(
            \Omnyfy\Vendor\Model\Vendor::ENTITY,
            $conn
        );

        $locationDefaultAttributeSetId = $this->getEntityDefaultAttributeSetId(
            \Omnyfy\Vendor\Model\Location::ENTITY,
            $conn
        );

        if (!$this->isTypeExists($conn)) {
            $conn->insert($tableName, [
                'type_id' => 1,
                'type_name' => 'Default',
                'search_by' => 0,
                'view_mode' => 0,
                'vendor_attribute_set_id' => $vendorDefaultAttributeSetId,
                'location_attribute_set_id' => $locationDefaultAttributeSetId,
                'status' => 1
            ]);
        }

        $staticFields = ['name', 'status', 'email'];
        $vendorAttrTable = $conn->getTableName('omnyfy_vendor_eav_attribute');
        foreach ($staticFields as $attributeCode) {
            $attributeId = $this->getAttributeId($attributeCode, $vendorEntityTypeId, $eavAttrTable, $conn);
            if (empty($attributeId)) {
                continue;
            }
            $this->updateAttribute($attributeId, 'is_visible', '0', $vendorAttrTable, $conn);
        }

        $table = $conn->getTableName('omnyfy_vendor_eav_attribute');
        if ($setup->tableExists($table)) {
            $toHide = [
                'vendor_id',
                'priority',
                'location_name',
                'description',
                'address',
                'suburb',
                'region',
                'country',
                'postcode',
                'status',
                'region_id',
                'booking_lead_time',
                'timezone',
                'is_warehouse',
                'rad_lon',
                'rad_lat',
                'cos_lat',
                'sin_lat',
                'lon',
                'lat',
                'opening_hours',
                'holiday_hours',
                'latitude',
                'longitude',
            ];

            $eavAttrTable = $conn->getTableName('eav_attribute');
            $locationEntityTypeId = $this->getEntityTypeId(\Omnyfy\Vendor\Model\Location::ENTITY, $conn);
            foreach ($toHide as $code) {
                $attributeId = $this->getAttributeId($code, $locationEntityTypeId, $eavAttrTable, $conn);
                if (empty($attributeId)) {
                    continue;
                }

                $this->updateAttribute($attributeId, 'is_visible', 0, $table, $conn);
            }

            $toHideVendor = [
                'name',
                'status',
                'email',
                'abn',
                'subscription_status',
                'subscription_start',
                'subscription_end',
            ];

            $vendorEntityTypeId = $this->getEntityTypeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, $conn);
            foreach ($toHideVendor as $code) {
                $attributeId = $this->getAttributeId($code, $vendorEntityTypeId, $eavAttrTable, $conn);
                if (empty($attributeId)) {
                    continue;
                }

                $this->updateAttribute($attributeId, 'is_visible', 0, $table, $conn);
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    private function getEntityTypeId($entityTypeCode, $conn)
    {
        $table = $conn->getTableName('eav_entity_type');
        $select = $conn->select()
            ->from($table, ['entity_type_id'])
            ->where('entity_type_code=?', $entityTypeCode);

        return $conn->fetchOne($select);
    }

    private function getEntityDefaultAttributeSetId($entityTypeCode, $conn)
    {
        $table = $conn->getTableName('eav_entity_type');
        $select = $conn->select()
            ->from($table, ['default_attribute_set_id'])
            ->where('entity_type_code=?', $entityTypeCode);

        return $conn->fetchOne($select);
    }

    private function getAttributeId($attributeCode, $typeId, $table, $conn)
    {
        $select = $conn->select()
            ->from($table, ['attribute_id'])
            ->where('entity_type_id=?', $typeId)
            ->where('attribute_code=?', $attributeCode);

        return $conn->fetchOne($select);
    }

    private function updateAttribute($attributeId, $column, $value, $table, $conn)
    {
        $conn->update(
            $table,
            [$column => $value],
            ['attribute_id=?' => $attributeId]
        );
    }

    private function isRoleExists($installer, $roleName)
    {
        $roleIds = $this->omnyfyHelper->getRoleIdsByName($roleName, $installer);
        if (!empty($roleIds)) {
            return true;
        }

        return false;
    }

    /**
     * Check if vendor type was added
     * This function for migrating 2.2 to 2.4
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $conn
     *
     * @return boolean
     */
    private function isTypeExists($conn) {
        $table = $conn->getTableName('omnyfy_vendor_vendor_type');
        $query = $conn->select()->from($table, 'type_id');
        $result = $conn->fetchOne($query);

        return !empty($result) ? true : false;
    }
}
