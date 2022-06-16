<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 14:40
 */
namespace Omnyfy\Approval\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $conn = $setup->getConnection();

        $table = $conn->getTableName('omnyfy_approval_product');
        if (!$setup->tableExists($table)) {
            $approvalProductTable = $conn->newTable($table)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Approval product record ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Product ID'
                )
                ->addColumn(
                    'sku',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'SKU'
                )
                ->addColumn(
                    'product_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Product Name'
                )
                ->addColumn(
                    'vendor_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Vendor ID'
                )
                ->addColumn(
                    'vendor_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Vendor Name'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Status'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->addIndex(
                    $setup->getIdxName(
                        'omnyfy_approval_product',
                        ['product_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['product_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'omnyfy_approval_product',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $setup->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName(
                        'omnyfy_approval_product',
                        'vendor_id',
                        'omnyfy_vendor_vendor_entity',
                        'entity_id'
                    ),
                    'vendor_id',
                    $setup->getTable('omnyfy_vendor_vendor_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
            ;

            $conn->createTable($approvalProductTable);
        }

        $table = $conn->getTableName('omnyfy_approval_product_history');
        if (!$setup->tableExists($table)) {
            $historyTable = $conn->newTable($table)
                ->addColumn(
                    'history_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'History ID'
                )
                ->addColumn(
                    'parent_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Parent ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Product ID'
                )
                ->addColumn(
                    'comment',
                    Table::TYPE_TEXT,
                    1024,
                    ['nullable' => true],
                    'Comment'
                )
                ->addColumn(
                    'before_status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true, 'unsigned' => true],
                    'Before Status'
                )
                ->addColumn(
                    'after_status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true, 'unsigned' => true],
                    'After Status'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addForeignKey(
                    $setup->getFkName('omnyfy_approval_product_history', 'parent_id', 'omnyfy_approval_product', 'id'),
                    'parent_id',
                    'omnyfy_approval_product',
                    'id',
                    AdapterInterface::FK_ACTION_CASCADE
                )
                ->addForeignKey(
                    $setup->getFkName('omnyfy_approval_product_history', 'product_id', 'catalog_product_entity', 'entity_id'),
                    'product_id',
                    'catalog_product_entity',
                    'entity_id',
                    AdapterInterface::FK_ACTION_CASCADE
                )
            ;

            $conn->createTable($historyTable);
        }
    }
}
 