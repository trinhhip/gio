<?php
namespace Omnyfy\Webhook\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements  UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $webhookEventHistoryTable = 'omnyfy_webhook_event_history';

        $version = $context->getVersion();
        $connection = $setup->getConnection();

        if (version_compare($version, '1.0.1') < 0) {
            $connection->addColumn(
                $setup->getTable($webhookEventHistoryTable), 'body', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'Webhook body sent',
                ]
            );
            $connection->addColumn(
                $setup->getTable($webhookEventHistoryTable), 'status', [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => '1',
                    'nullable' => false,
                    'comment' => 'Webhook sent status',
                ]
            );
        }

        if (version_compare($version, '1.0.2') < 0) {
            $connection->addColumn(
                $setup->getTable($webhookEventHistoryTable), 'event_id', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => false,
                    'comment' => 'Event Id',
                ]
            );

            $connection->addIndex(
                $setup->getTable($webhookEventHistoryTable),
                $setup->getIdxName(
                    $setup->getTable($webhookEventHistoryTable),
                    ['event_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['event_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        $webhookScheduleTable = 'omnyfy_webhook_event_schedule';
        if (version_compare($version, '1.0.3') < 0) {
            $connection->dropForeignKey(
                $setup->getTable($webhookScheduleTable),
                $installer->getFkName('omnyfy_webhook_event_schedule', 'webhook_type_id', 'omnyfy_webhook_type', 'entity_id')
            );
            $connection->changeColumn(
                $setup->getTable($webhookScheduleTable),
                'webhook_type_id',
                'webhook_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'Webhook Id',
                ]
            );
            $connection->addForeignKey(
                $installer->getFkName('omnyfy_webhook_event_schedule', 'webhook_id', 'omnyfy_webhook', 'entity_id'),
                $webhookScheduleTable,
                'webhook_id',
                $installer->getTable('omnyfy_webhook'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        }

        if (version_compare($version, '1.0.4') < 0) {
            $connection->changeColumn(
                $setup->getTable($webhookEventHistoryTable),
                'body',
                'body',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => null,
                    'nullable' => false,
                    'comment' => 'Webhook body sent',
                ]
            );
        }
    }
}
