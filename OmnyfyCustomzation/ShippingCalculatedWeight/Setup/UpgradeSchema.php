<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Setup;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $ShippingCalculatedWeightTable = $setup->getTable('shipping_calculate_weight');
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $connection->addColumn(
                $ShippingCalculatedWeightTable,
                'surcharge_apply',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'comment' => 'Surcharge Apply'
                ]
            );
            $connection->addColumn(
                $ShippingCalculatedWeightTable,
                'surcharge_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,2',
                    'nullable' => true,
                    'comment' => 'Surcharge Fee',
                    'default' => 0.0
                ]
            );
            $connection->addColumn(
                $ShippingCalculatedWeightTable,
                'priority',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'comment' => 'Rule Priority',
                    'default' => 0
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $connection->addColumn(
                $ShippingCalculatedWeightTable,
                'weight_from',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,2',
                    'nullable' => true,
                    'comment' => 'Weight From',
                    'default' => 0.0
                ]
            );
            $connection->addColumn(
                $ShippingCalculatedWeightTable,
                'weight_to',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,2',
                    'nullable' => true,
                    'comment' => 'Weight To',
                    'default' => 99999
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $connection->changeColumn(
                $ShippingCalculatedWeightTable,
                'ship_to_country',
                'ship_to_country',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 1000,
                    'nullable' => true,
                    'comment' => 'Ship To Country'
                ]
            );
            $connection->changeColumn(
                $ShippingCalculatedWeightTable,
                'ship_from_country',
                'ship_from_country',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 1000,
                    'nullable' => true,
                    'comment' => 'Ship From Country'
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.0', '<')){
            $connection->addColumn(
                $ShippingCalculatedWeightTable,
                'type',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'comment' => 'Calc Type'
                ]
            );
            $connection->addColumn(
                $ShippingCalculatedWeightTable,
                'price',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,2',
                    'nullable' => true,
                    'comment' => 'Price',
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')){
            $connection->changeColumn(
                $ShippingCalculatedWeightTable,
                'round_factor',
                'round_factor',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '12,2',
                    'nullable' => true,
                    'comment' => 'Round Factor',
                    'default' => '0.5'
                ]
            );
        }
    }
}
