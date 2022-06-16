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

namespace OmnyfyCustomzation\PriceToQuote\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use OmnyfyCustomzation\PriceToQuote\Model\Product\Attribute\Source\PriceToBeQuoted;

class UpgradeData implements UpgradeDataInterface
{
    const PRICE_TO_BE_QUOTE = 'price_to_be_quoted';

    /**
     * @var EavSetupFactory
     */
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
    )
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), "1.1.0", "<")) {
            $eavSetup->removeAttribute(Product::ENTITY, self::PRICE_TO_BE_QUOTE);
            $eavSetup->addAttribute(
                Product::ENTITY,
                self::PRICE_TO_BE_QUOTE,
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Price To Be Quoted',
                    'input' => 'select',
                    'class' => '',
                    'source' => PriceToBeQuoted::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '0',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'group' => 'General',
                    'sort_order' => 1000,
                ]
            );
        }
        if (version_compare($context->getVersion(), "1.1.2", "<")) {
            $this->creatPriceToQuoteTable($setup);
        }
    }

    private function creatPriceToQuoteTable(ModuleDataSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable('vermillion_product_to_quote')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'customer_name',
            Table::TYPE_TEXT,
            255,
            ['default' => null, 'nullable' => false],
            'Customer Name'
        )->addColumn(
            'customer_email',
            Table::TYPE_TEXT,
            255,
            ['default' => null, 'nullable' => false],
            'Customer Email'
        )->addColumn(
            'is_sent_email',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'unsigned' => true, 'default' => 0],
            'Is Sent Email'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Product Id'
        )->addColumn(
            'product_sku',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'unsigned' => true],
            'Product Sku'
        )->addColumn(
            'inquiry',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'unsigned' => true],
            'Inquiry'
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
        $setup->getConnection()->createTable($table);
    }
}
