<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Vendor\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class AddEavAttribute implements DataPatchInterface
{
    protected $vendorSetupFactory;
    private $eavSetupFactory;
    private $widgetFactory;
    protected $scopeConfigInterface;
    protected $moduleDataSetup;
    protected $storeManager;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Omnyfy\Vendor\Setup\VendorSetupFactory $vendorSetupFactory,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->vendorSetupFactory = $vendorSetupFactory;
        $this->widgetFactory = $widgetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }
    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $setup = $this->moduleDataSetup;

        $vendorSetup = $this->vendorSetupFactory->create(['setup' => $setup]);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $vendorSetup->installEntities();

        $locationEntity = \Omnyfy\Vendor\Model\Location::ENTITY;
        $vendorEntity = \Omnyfy\Vendor\Model\Vendor::ENTITY;

        $locationEntity = \Omnyfy\Vendor\Model\Location::ENTITY;
        $vendorSetup->addAttribute(
            $locationEntity,
            'region_id',
            [
                'type'          => 'static',
                'input'         => 'select',
                'required'      => true,
                'sort_order'    => 400,
                'visible'       => true,
                'system'        => false,
                'searchable'    => true,
                'used_in_listing' => true,
                'frontend_input' => 'text',
            ]
        );
        $vendorSetup->addAttribute(
            $locationEntity,
            'latitude',
            [
                'type'          => 'decimal',
                'label'         => 'Latitude',
                'input'         => 'text',
                'required'      => false,
                'sort_order'    => 650,
                'visible'       => true,
                'system'        => false,
                'searchable'    => true,
                'used_in_listing' => true,
            ]
        );

        $vendorSetup->addAttribute(
            $locationEntity,
            'longitude',
            [
                'type'          => 'decimal',
                'label'         => 'Longitude',
                'input'         => 'text',
                'required'      => false,
                'sort_order'    => 700,
                'visible'       => true,
                'system'        => false,
                'searchable'    => true,
                'used_in_listing' => true,
            ]
        );

        $vendorSetup->addAttribute(
            $locationEntity,
            'region_id',
            [
                'type'          => 'static',
                'label'         => 'Region ID',
                'input'         => 'text',
                'required'      => true,
                'sort_order'    => 500,
                'visible'       => true,
                'system'        => false,
                'searchable'    => true,
                'used_in_listing' => true,
            ]
        );
        $vendorSetup->addAttribute(
            $locationEntity,
            'is_warehouse',
            [
                'type'          => 'static',
                'label'         => 'Is Warehouse',
                'input'         => 'select',
                'required'      => true,
                'sort_order'    => 650,
                'visible'       => true,
                'system'        => false,
                'searchable'    => true,
                'used_in_listing' => true,
                'source_model'  => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ]
        );
        $attributeModel = $vendorSetup->getEntityType($vendorEntity, 'attribute_model');
        if ('Omnyfy\\Vendor\\Model\\Resource\\Vendor\\Eav\\Attribute' !== $attributeModel) {
            $vendorSetup->updateEntityType(
                $vendorEntity,
                'attribute_model',
                'Omnyfy\\Vendor\\Model\\Resource\\Vendor\\Eav\\Attribute'
            );
        }

        $attributeModel = $vendorSetup->getEntityType($locationEntity, 'attribute_model');
        if ('Omnyfy\\Vendor\\Model\\Resource\\Eav\\Attribute' !== $attributeModel) {
            $vendorSetup->updateEntityType(
                $locationEntity,
                'attribute_model',
                'Omnyfy\\Vendor\\Model\\Resource\\Eav\\Attribute'
            );
        }

        $vendorSetup->updateAttribute(
            $vendorEntity,
            'status',
            [
                'frontend_input' => 'select',
                'frontend_label' => 'Status',
                'source_model' => 'Omnyfy\\Vendor\\Model\\Source\\Status'
            ]
        );

        $vendorSetup->updateAttribute(
            $vendorEntity,
            'status',
            [
                'frontend_input' => 'select',
                'frontend_label' => 'Status',
                'source_model' => 'Omnyfy\\Vendor\\Model\\Source\\Status'
            ]
        );
        $vendorSetup->updateAttribute(
            $locationEntity,
            'is_warehouse',
            [
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ]
        );

        $vendorSetup->addAttribute(
            $locationEntity,
            'lon',
            [
                'type'          => 'static',
                'label'         => 'Longitude',
                'input'         => 'text',
                'required'      => true,
                'sort_order'    => 650,
                'visible'       => false,
                'system'        => true,
                'searchable'    => false,
                'used_in_listing' => false
            ]
        );
        $vendorSetup->addAttribute(
            $locationEntity,
            'lat',
            [
                'type'          => 'static',
                'label'         => 'Latitude',
                'input'         => 'text',
                'required'      => true,
                'sort_order'    => 650,
                'visible'       => false,
                'system'        => true,
                'searchable'    => false,
                'used_in_listing' => false
            ]
        );

        $vendorSetup->addAttribute(
            $locationEntity,
            'rad_lon',
            [
                'type'          => 'static',
                'label'         => 'Radians of Longitude',
                'input'         => 'text',
                'required'      => true,
                'sort_order'    => 650,
                'visible'       => false,
                'system'        => true,
                'searchable'    => true,
                'used_in_listing' => true
            ]
        );
        $vendorSetup->addAttribute(
            $locationEntity,
            'rad_lat',
            [
                'type'          => 'static',
                'label'         => 'Radians of Latitude',
                'input'         => 'text',
                'required'      => true,
                'sort_order'    => 650,
                'visible'       => false,
                'system'        => true,
                'searchable'    => true,
                'used_in_listing' => true
            ]
        );
        $vendorSetup->addAttribute(
            $locationEntity,
            'cos_lat',
            [
                'type'          => 'static',
                'label'         => 'Cosine of Latitude',
                'input'         => 'text',
                'required'      => true,
                'sort_order'    => 650,
                'visible'       => false,
                'system'        => true,
                'searchable'    => true,
                'used_in_listing' => true
            ]
        );
        $vendorSetup->addAttribute(
            $locationEntity,
            'sin_lat',
            [
                'type'          => 'static',
                'label'         => 'Sine of Latitude',
                'input'         => 'text',
                'required'      => true,
                'sort_order'    => 650,
                'visible'       => false,
                'system'        => true,
                'searchable'    => true,
                'used_in_listing' => true
            ]
        );
        $toRemove = ['fax', 'social_media'];
        foreach ($toRemove as $code) {
            $vendorSetup->removeAttribute($vendorEntity, $code);
        }

        $toVarchar = ['abn'];
        foreach ($toVarchar as $code) {
            $vendorSetup->updateAttribute($vendorEntity, $code, 'frontend_input', 'text');
        }

        $toTextarea = ['description', 'shipping_policy', 'return_policy', 'payment_policy', 'marketing_policy'];
        foreach ($toTextarea as $code) {
            $vendorSetup->updateAttribute($vendorEntity, $code, 'frontend_input', 'textarea');
        }

        $toImage = ['logo', 'banner'];
        foreach ($toImage as $code) {
            $vendorSetup->updateAttribute($vendorEntity, $code, 'frontend_input', 'image');
            $vendorSetup->updateAttribute(
                $vendorEntity,
                $code,
                'backend_model',
                'Omnyfy\Vendor\Model\Vendor\Attribute\Backend\Media'
            );
        }

        $toLabel = [
            'name' => 'Vendor Name',
            'status' => 'Status',
            'email' => 'Email',
            'abn' => 'ABN',
            'logo' => 'Logo',
            'banner' => 'Banner',
            'shipping_policy' => 'Shipping Policy',
            'return_policy' => 'Return Policy',
            'payment_policy' => 'Payment Policy',
            'marketing_policy' => 'Marketing Policy',
            'address' => 'Address',
            'phone' => 'Phone',
            'description' => 'Description'
        ];

        foreach ($toLabel as $code => $label) {
            $vendorSetup->updateAttribute($vendorEntity, $code, 'frontend_label', $label);
        }

        $toLabel = [
            'vendor_id' => 'Vendor Id',
            'priority' => 'Priority',
            'location_name' => 'Location name',
            'description' => 'Description',
            'address' => 'Address',
            'suburb' => 'Suburb',
            'region' => 'Region',
            'country' => 'Country',
            'postcode' => 'Postcode',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'status' => 'Status'
        ];

        foreach ($toLabel as $code => $label) {
            $vendorSetup->updateAttribute($locationEntity, $code, 'frontend_label', $label);
        }
        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'omnyfy_dimensions_length')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'omnyfy_dimensions_length',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Omnyfy Dimension Length',
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
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'omnyfy_dimensions_width')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'omnyfy_dimensions_width',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Omnyfy Dimension Width',
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
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'omnyfy_dimensions_height')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'omnyfy_dimensions_height',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Omnyfy Dimension Height',
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
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }
        $toLabel = [
            'address' => 'Address',
            'phone' => 'Phone',
            'description' => 'Description'
        ];

        foreach ($toLabel as $code => $label) {
            $vendorSetup->updateAttribute($vendorEntity, $code, 'frontend_label', $label);
        }

        $toLabel = [
            'vendor_id' => 'Vendor Id',
            'priority' => 'Priority',
            'location_name' => 'Location name',
            'description' => 'Description',
            'address' => 'Address',
            'suburb' => 'Suburb',
            'region' => 'Region',
            'country' => 'Country',
            'postcode' => 'Postcode',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'status' => 'Status'
        ];

        foreach ($toLabel as $code => $label) {
            $vendorSetup->updateAttribute($locationEntity, $code, 'frontend_label', $label);
        }
        $toHideLocation = [
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
            'is_warehouse',
            'rad_lon',
            'rad_lat',
            'cos_lat',
            'sin_lat',
            'lon',
            'lat',
            'latitude',
            'longitude',
        ];

        foreach ($toHideLocation as $code) {
            $vendorSetup->updateAttribute($locationEntity, $code, 'is_visible', 0);
        }


        $toHideVendor = [
            'name',
            'status',
            'email',
            'abn'
        ];

        foreach ($toHideVendor as $code) {
            $vendorSetup->updateAttribute($vendorEntity, $code, 'is_visible', 0);
        }
        if (!$eavSetup->getAttributeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, 'vfree_shipping_config')) {
            $eavSetup->addAttribute(
                \Omnyfy\Vendor\Model\Vendor::ENTITY,
                'vfree_shipping_config',
                [
                    'type' => 'int',
                    'label' => 'Enable Free Shipping Message',
                    'input' => 'select',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'required' => true,
                    'default' => '0',
                    'sort_order' => 100,
                    'visible' => true,
                    'system' => false,
                    'searchable' => true,
                    'used_in_listing' => true
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, 'vfree_shipping_threshold')) {
            $eavSetup->addAttribute(
                \Omnyfy\Vendor\Model\Vendor::ENTITY,
                'vfree_shipping_threshold',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Free Shipping Threshold',
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
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        if (!$eavSetup->getAttributeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, 'vfree_shipping_message')) {
            $eavSetup->addAttribute(
                \Omnyfy\Vendor\Model\Vendor::ENTITY,
                'vfree_shipping_message',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Free Shipping Message',
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
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        if ($eavSetup->getAttributeId(\Omnyfy\Vendor\Model\Vendor::ENTITY, 'vfree_shipping_message')) {
            $eavSetup->removeAttribute(
                \Omnyfy\Vendor\Model\Vendor::ENTITY,
                'vfree_shipping_message'
            );
        }
        if (!$vendorSetup->getAttributeId(\Omnyfy\Vendor\Model\Location::ENTITY, 'location_contact_name')) {
            $vendorSetup->addAttribute(
                $locationEntity,
                'location_contact_name',
                [
                    'type'          => 'static',
                    'label'         => 'Location Contact Name',
                    'input'         => 'text',
                    'required'      => true,
                    'sort_order'    => 700,
                    'visible'       => true,
                    'system'        => false,
                    'searchable'    => true,
                    'used_in_listing' => true,
                ]
            );
        }

        if (!$vendorSetup->getAttributeId(\Omnyfy\Vendor\Model\Location::ENTITY, 'location_contact_phone')) {
            $vendorSetup->addAttribute(
                $locationEntity,
                'location_contact_phone',
                [
                    'type'          => 'static',
                    'label'         => 'Location Contact Phone',
                    'input'         => 'text',
                    'required'      => true,
                    'sort_order'    => 710,
                    'visible'       => true,
                    'system'        => false,
                    'searchable'    => true,
                    'used_in_listing' => true,
                ]
            );
        }

        if (!$vendorSetup->getAttributeId(\Omnyfy\Vendor\Model\Location::ENTITY, 'location_contact_email')) {
            $vendorSetup->addAttribute(
                $locationEntity,
                'location_contact_email',
                [
                    'type'          => 'static',
                    'label'         => 'Location Contact Email',
                    'input'         => 'text',
                    'required'      => true,
                    'sort_order'    => 720,
                    'visible'       => true,
                    'system'        => false,
                    'searchable'    => true,
                    'used_in_listing' => true,
                ]
            );
        }
        if (!$vendorSetup->getAttributeId(\Omnyfy\Vendor\Model\Location::ENTITY, 'location_company_name')) {
            $vendorSetup->addAttribute(
                $locationEntity,
                'location_company_name',
                [
                    'type'          => 'static',
                    'label'         => 'Location Company Name',
                    'input'         => 'text',
                    'required'      => true,
                    'sort_order'    => 730,
                    'visible'       => true,
                    'system'        => false,
                    'searchable'    => true,
                    'used_in_listing' => true,
                ]
            );
        }
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'display_on_vendor_storefront',
            [
                'type'     => 'int',
                'label'    => 'Display Product Categories on Vendor Storefront',
                'source'   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'input'    => 'boolean',
                'required' => false,
                'sort_order' => 100,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Display Settings',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ]
        );

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
}
