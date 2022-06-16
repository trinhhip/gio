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

namespace OmnyfyCustomzation\Catalog\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    const DEFAULT_STORE_ID = 0;

    private $eavSetupFactory;
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var CollectionFactory
     */
    private $productCollection;
    /**
     * @var State
     */
    private $state;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ResourceConnection $resource
     * @param CollectionFactory $productCollection
     * @param State $state
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ResourceConnection $resource,
        CollectionFactory $productCollection,
        State $state
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->resource = $resource;
        $this->productCollection = $productCollection;
        $this->state = $state;
    }

    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            $connection = $this->resource->getConnection();
            $catalogProductTable = $connection->getTableName('catalog_category_product');
            $sql = $connection->select()->from(
                ['cp' => $catalogProductTable],
                [
                    'entity_id' => 'cp.entity_id',
                    'category_id' => 'cp.category_id',
                    'product_id' => 'cp.product_id',
                    'position' => 'cp.position'
                ]
            )->where('position < ?', 1000);
            $catalogProducts = $connection->fetchAll($sql);
            foreach ($catalogProducts as $catalogProduct) {
                $data = [
                    'entity_id' => $catalogProduct['entity_id'],
                    'category_id' => $catalogProduct['category_id'],
                    'product_id' => $catalogProduct['product_id'],
                    'position' => rand(100, 1000)
                ];
                $connection->insertOnDuplicate($catalogProductTable, $data);
            }
        }
        if (version_compare($context->getVersion(), "1.0.5", "<")) {
            $this->creatMadeToOrderAttribute($setup);
        }
    }

    private function creatMadeToOrderAttribute(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'made_to_order',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Made To Order',
                'input' => 'boolean',
                'class' => '',
                'source' => '',
                'global' => 1,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => '',
                'system' => 1,
                'group' => 'General',
                'option' => ['values' => [""]]
            ]
        );
    }
}
