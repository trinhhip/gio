<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Enquiry\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

class AddEavAttribute implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }
    /**
     * @inheritdoc
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'enable_enquiry',
            [
                'type' => 'varchar',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'label' => 'Enable Enquiry',
                'input' => 'select',
                'class' => '',
                'source' => 'Omnyfy\Enquiry\Model\Enquiries\Attribute\Source\Options',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
                'system' => 1,
                'group' => 'General'
            ]
        );

        $groupName = 'Manage Enquiry'; /* Label of your group*/
        $entityTypeId = $eavSetup->getEntityTypeId('catalog_product'); /* get entity type id so that attribute are only assigned to catalog_product */

	// get default attribute set id
	$attributeSetId = $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Product::ENTITY);
	$attributeGroupName = 'Manage Enquiry';

	$eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 19);
	$attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

	// Add existing attribute to group
	$attributeId = $eavSetup->getAttributeId($entityTypeId, 'enable_enquiry');
	$eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);

	
        $eavSetup->addAttribute(
            \Omnyfy\Vendor\Model\Vendor::ENTITY,
            'enquiry_for_vendor',
            [
                'type'          => 'int',
                'label'         => 'Enable Enquiry for Vendor',
                'input'         => 'select',
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required'      => true,
                'sort_order'    => 100,
                'visible'       => true,
                'system'        => false,
                'searchable'    => true,
		'used_in_listing' => true,
		'group'		=> 'General'
            ]
	);

        $eavSetup->addAttribute(
            \Omnyfy\Vendor\Model\Vendor::ENTITY,
            'enquiry_for_products',
            [
                'type'          => 'int',
                'label'         => 'Enable Enquiry for Products',
                'input'         => 'select',
                'source'        => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required'      => true,
                'sort_order'    => 200,
                'visible'       => true,
                'system'        => false,
                'searchable'    => true,
		'used_in_listing' => true,
		'group'		=> 'General'
            ]
	);

        $setup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function getVersion()
    {
         return '1.0.16';
    }

}

