<?php
namespace Omnyfy\Webhook\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'omnyfy_webhook_type'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('omnyfy_webhook_type')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Webhook Type ID'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Webhook Type'
        )->setComment(
            'Webhook Type table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'omnyfy_webhook'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('omnyfy_webhook')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => '1'],
            'Status'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            64,
            ['nullable' => false, 'unsigned' => true, 'default' => 0],
            'Store Id'
        )->addColumn(
            'webhook_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Webhook Type id'
        )->addColumn(
            'method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'API Method'
        )->addColumn(
            'endpoint_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Endpoint Url'
        )->addColumn(
            'content_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Content Type'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created at'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated time'
        )->addForeignKey(
            $installer->getFkName('omnyfy_webhook', 'webhook_type_id', 'omnyfy_webhook_type', 'entity_id'),
            'webhook_type_id',
            $installer->getTable('omnyfy_webhook_type'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('omnyfy_webhook', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Webhook table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'omnyfy_webhook_event_schedule'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('omnyfy_webhook_event_schedule')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Event schedule id'
        )->addColumn(
            'webhook_type_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            255,
            ['nullable' => false],
            'Webhook Type id'
        )->addColumn(
            'body',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Webhook Body'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            64,
            ['nullable' => false, 'unsigned' => true, 'default' => 0,],
            'Store Id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => '1'],
            'Status'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created at'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated time'
        )->addForeignKey(
            $installer->getFkName('omnyfy_webhook_event_schedule', 'webhook_type_id', 'omnyfy_webhook_type', 'entity_id'),
            'webhook_type_id',
            $installer->getTable('omnyfy_webhook_type'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('omnyfy_webhook_event_schedule', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Webhook Event Schedule table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'omnyfy_webhook_event_history'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('omnyfy_webhook_event_history')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'History id'
        )->addColumn(
            'webhook_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Webhook ID'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created at'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            255,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated time'
        )->addForeignKey(
            $installer->getFkName('omnyfy_webhook_event_history', 'webhook_id', 'omnyfy_webhook', 'entity_id'),
            'webhook_id',
            $installer->getTable('omnyfy_webhook'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Webhook history table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'omnyfy_webhook_event_response'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('omnyfy_webhook_event_response')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'History id'
        )->addColumn(
            'history_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Webhook history id'
        )->addColumn(
            'status_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            3,
            ['nullable' => false],
            'Response status'
        )->addColumn(
            'body',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Webhook body response'
        )->addForeignKey(
            $installer->getFkName('omnyfy_webhook_event_response', 'history_id', 'omnyfy_webhook_event_history', 'entity_id'),
            'history_id',
            $installer->getTable('omnyfy_webhook_event_history'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Sales channel vendor table'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
