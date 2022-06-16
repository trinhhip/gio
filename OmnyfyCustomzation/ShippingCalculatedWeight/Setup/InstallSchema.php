<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Setup;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer
            ->getConnection()
            ->newTable($installer->getTable('shipping_calculate_weight'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'unsigned' => true, 'default' => 0]
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false]
            )->addColumn(
                'ship_from_country',
                Table::TYPE_TEXT,
                1000,
                ['default' => null, 'nullable' => false]
            )->addColumn(
                'ship_to_country',
                Table::TYPE_TEXT,
                1000,
                ['default' => null, 'nullable' => false]
            )->addColumn(
                'calc_formula',
                Table::TYPE_TEXT,
                255,
                ['default' => null, 'nullable' => false]
            )->addColumn(
                'round_factor',
                Table::TYPE_DECIMAL,
                '12,1',
                ['nullable' => false, 'unsigned' => true, 'default' => '0.5']
            );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
