<?php
/**
 * Lucas
 * Copyright (C) 2019 
 * 
 * This file is part of OmnyfyCustomzation/ConfigurableProduct.
 * 
 * OmnyfyCustomzation/ConfigurableProduct is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\ConfigurableProduct\Setup;

use Magento\Catalog\Model\Product;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use OmnyfyCustomzation\ConfigurableProduct\Model\Product\Attribute\Source\PriceDisplay;

class UpgradeData implements UpgradeDataInterface
{

    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), "1.0.0", "<")) {
        
            $eavSetup->addAttribute(
                Product::ENTITY,
                'display_higher_price',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Display Higher Price',
                    'input' => 'boolean',
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
                    'unique' => false,
                    'apply_to' => 'configurable',
                    'system' => 1,
                    'group' => 'General'
                ]
            );
        }
        if (version_compare($context->getVersion(), "1.0.1", "<")){
            $eavSetup->removeAttribute(Product::ENTITY, 'display_higher_price');
            $eavSetup->addAttribute(
                Product::ENTITY,
                'price_display',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Price Display',
                    'input' => 'select',
                    'class' => '',
                    'source' => PriceDisplay::class,
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
                    'unique' => false,
                    'apply_to' => 'configurable',
                    'system' => 1,
                    'group' => 'General',
                    'option' => ''
                ]
            );
        }
    }
}
