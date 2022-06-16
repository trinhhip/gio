<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/B2C.
 *
 * OmnyfyCustomzation/B2C is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\B2C\Setup;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use OmnyfyCustomzation\B2C\Model\Product\Attribute\Source\ForRetail;

class UpgradeData implements UpgradeDataInterface
{

    private $eavSetupFactory;
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $eavSetup->removeAttribute(Product::ENTITY, 'for_retail');

            $eavSetup->addAttribute(
                Product::ENTITY,
                'for_retail',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'For Retail',
                    'input' => 'select',
                    'class' => '',
                    'source' => ForRetail::class,
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => 0,
                    'searchable' => true,
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
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->createNewTableApproval($setup);
        }
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->updateCustomerAttribute($setup);
        }
    }

    private function createNewTableApproval(ModuleDataSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $connection->dropTable($connection->getTableName('b2c_customer_approval'));
        $table = $connection->newTable($installer->getTable('b2c_customer_approval'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'unsigned' => true]
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    private function updateCustomerAttribute(ModuleDataSetupInterface $setup)
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerSetup->updateAttribute(Customer::ENTITY,
            'business_type',
            'is_required',
            false
        );
    }
}
