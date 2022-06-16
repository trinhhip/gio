<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/ShippingCalculatedWeight.
 *
 * OmnyfyCustomzation/ShippingCalculatedWeight is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OmnyfyCustomzation\ShippingCalculatedWeight\Setup;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    const DEFAULT_STORE_ID = 0;

    private $eavSetupFactory;
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param ResourceConnection $resource
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ResourceConnection $resource
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->resource = $resource;
    }

    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), "1.0.1", "<")) {

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'ship_from_country',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Ship From Country',
                    'input' => 'select',
                    'class' => '',
                    'source' => \OmnyfyCustomzation\ShippingCalculatedWeight\Model\Product\Attribute\Source\ShipFromCountry::class,
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => null,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'system' => 1,
                    'group' => 'General',
                    'option' => ''
                ]
            );
        }
        if (version_compare($context->getVersion(), "1.0.2", "<")) {
            $this->updateShipFromCountryForAllProduct();
        }
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            $this->addCalcWeight($eavSetup);
        }

    }

    private function updateShipFromCountryForAllProduct()
    {
        $connection = $this->resource->getConnection();
        $productEntityTable = $connection->getTableName('catalog_product_entity_varchar');
        $vendorProductTable = $connection->getTableName('omnyfy_vendor_vendor_product');
        $vendorSignUpTable = $connection->getTableName('omnyfy_vendor_signup');
        $KycTable = $connection->getTableName('omnyfy_vendor_kyc_details');


        $sql = $connection->select()->from(
            ['vs' => $vendorSignUpTable],
            [
                'product_id' => 'vp.product_id',
                'vendor_id' => 'kyc.vendor_id',
                'country' => 'vs.country'

            ]
        )->joinInner(
            ['kyc' => $KycTable],
            'vs.id = kyc.signup_id'
        )->joinInner(
            ['vp' => $vendorProductTable],
            'vp.vendor_id = kyc.vendor_id'
        );
        $productCountry = $connection->fetchAll($sql);
        $attributeId = $this->getAttributeId($connection);
        foreach ($productCountry as $country) {
            $data = [
                'attribute_id' => $attributeId,
                'store_id' => self::DEFAULT_STORE_ID,
                'entity_id' => $country['product_id'],
                'value' => $country['country']
            ];
            $connection->insertOnDuplicate($productEntityTable, $data, ['entity_id', 'value']);
        }
    }

    private function getAttributeId($connection)
    {
        $eavTable = $connection->getTableName('eav_attribute');
        $eavSql = $connection->select()->from(
            ['e' => $eavTable],
            [
                'attribute_id' => 'e.attribute_id',
            ]
        )->where('e.attribute_code = ?', 'ship_from_country');
        return $connection->fetchOne($eavSql);
    }

    private function addCalcWeight($eavSetup)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            'override_csw',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Override CSW',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'system' => 1,
                'group' => 'General',
            ]
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            'calculated_shipping_weight',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Calculated Shipping Weight',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'is_used_in_grid' => true,
                'is_used_for_promo_rules' => true,
                'unique' => false,
                'apply_to' => '',
                'system' => 1,
                'group' => 'General',
            ]
        );
    }


}