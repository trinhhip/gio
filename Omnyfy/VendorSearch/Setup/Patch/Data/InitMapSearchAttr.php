<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorSearch\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Omnyfy\VendorSearch\Helper\MapSearchData;

class InitMapSearchAttr implements DataPatchInterface
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
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        if (!$eavSetup->getAttributeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, 'latitude')) {
            $eavSetup->addAttribute(
                \Omnyfy\Vendor\Model\Vendor::ENTITY,
                MapSearchData::VENDOR_LATITUDE,
                [
                    'type' => 'text',
                    'group' => 'Location',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Latitude',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_listing' => true,
                    'unique' => false
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, 'longitude')) {
            $eavSetup->addAttribute(
                \Omnyfy\Vendor\Model\Vendor::ENTITY,
                MapSearchData::VENDOR_LONGITUDE,
                [
                    'type' => 'text',
                    'group' => 'Location',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Longitude',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_listing' => true,
                    'unique' => false,
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, 'vendor_map_search_distance')) {
            $eavSetup->addAttribute(
                \Omnyfy\Vendor\Model\Vendor::ENTITY,
                MapSearchData::VENDOR_MAP_SEARCH_DISTANCE,
                [
                    'type' => 'int',
                    'group' => 'Location',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Distance',
                    'input' => 'select',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => 2,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_listing' => true,
                    'unique' => false,
                    'position' => '-1',
                    'option' => ['values' => ['10', '20', '50', '100', '250', '500']],
                ]
            );
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
