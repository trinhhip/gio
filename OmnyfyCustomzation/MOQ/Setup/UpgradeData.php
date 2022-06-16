<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/MOQ.
 *
 * OmnyfyCustomzation/MOQ is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\MOQ\Setup;

use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

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
    )
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), "1.0.5", "<")) {
//            $eavSetup->removeAttribute(Product::ENTITY, 'min_order_qty');
            $eavSetup->addAttribute(
                Product::ENTITY,
                'min_order_qty',
                [
                    'type' => 'varchar',
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'frontend' => '',
                    'label' => 'Minimum Order Quantity',
                    'input' => 'multiselect',
                    'class' => '',
                    'source' => '',
                    'global' => 2,
                    'visible' => true,
                    'required' => true,
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
                    'option' => ['values' => $this->getOptions()]
                ]
            );
//            $this->updateAttributeValue($setup);
        }
    }

    private function getOptions()
    {
        return [
            1 => '1 unit',
            10 => '10 units and below',
            50 => '50 units and below',
            100 => '100 units and below',
            500 => '500 units and below',
            99999 => '500 units and above'
        ];
    }

    private function updateAttributeValue(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $eavTable = $connection->getTableName('eav_attribute');
        $stockTable = $connection->getTableName('cataloginventory_stock_item');
        $moqAttrTable = $connection->getTableName('catalog_product_entity_varchar');

        $sqlEav = $connection->select()->from(
            ['ev' => $eavTable],
            [
                'attribute_code' => 'ev.attribute_code',
                'attribute_id' => 'ev.attribute_id',
            ]
        )->where('ev.attribute_code = ?', 'min_order_qty');
        $attributes = $connection->fetchAll($sqlEav);
        if (isset($attributes[0])) {
            $moqAttrId = $attributes[0]['attribute_id'];
            $sqlStock = $connection->select()->from(
                ['st' => $stockTable],
                [
                    'product_id' => 'st.product_id',
                    'min_sale_qty' => 'st.min_sale_qty',
                ]
            )->where('st.website_id = 0');
            $optionIds = $this->getOptionsId($connection);
            $optionsMinQty = [];
            $options = $this->getOptions();
            foreach ($optionIds as $optionId) {
                $minQty = array_search($optionId['value'], $options);
                $optionsMinQty[] = [
                    'qty' => $minQty,
                    'value' => $optionId['option_id']
                ];
            }
            $stockData = $connection->fetchAll($sqlStock);
            $attributesData = $this->prepareMinOderQtyInsert($stockData, $moqAttrId, $optionsMinQty);
            $connection->insertMultiple($moqAttrTable, $attributesData);

        }
    }

    private function prepareMinOderQtyInsert($stockData, $moqAttrId, $optionsMinQty)
    {
        $data = [];
        foreach ($stockData as $item) {
            $minSaleQty = (int)$item['min_sale_qty'];
            $minValue = [];
            foreach ($optionsMinQty as $qty => $optionValue) {
                if ($minSaleQty > 500) {
                    $minValue = end($optionsMinQty)['value'];
                } elseif ($minSaleQty < $optionValue['qty'] || $minSaleQty == $optionValue['qty']) {
                    $minValue = $optionValue['value'];
                    break;
                }
            }
            $data[] = [
                'attribute_id' => $moqAttrId,
                'store_id' => 0,
                'entity_id' => $item['product_id'],
                'value' => is_array($minValue) ? implode(',', $minValue) : $minValue
            ];
        }
        return $data;
    }

    private function getOptionsId(AdapterInterface $connection)
    {
        $optionTable = $connection->getTableName('eav_attribute_option_value');
        $options = $this->getOptions();
        $sqlOption = $connection->select()->from(
            ['o' => $optionTable],
            [
                'option_id' => 'o.option_id',
                'value' => 'o.value'
            ])
            ->where('o.store_id = 0')
            ->where('o.value IN (?)', $options);
        return $connection->fetchAll($sqlOption);
    }
}
