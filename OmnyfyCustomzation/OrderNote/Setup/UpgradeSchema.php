<?php

namespace OmnyfyCustomzation\OrderNote\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const ORDER_NOTE_ATTRIBUTE = 'order_note';

    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;

        $installer->startSetup();

        if(version_compare($context->getVersion(), '1.2.0', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable( 'quote_item' ),
                self::ORDER_NOTE_ATTRIBUTE,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'order note'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable( 'sales_order_item' ),
                self::ORDER_NOTE_ATTRIBUTE,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'order note'
                ]
            );
        }

        $installer->endSetup();
    }
}