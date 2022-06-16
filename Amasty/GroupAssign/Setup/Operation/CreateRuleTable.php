<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GroupAssign
 */


namespace Amasty\GroupAssign\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\GroupAssign\Api\Data\RuleInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\App\ProductMetadataInterface;

class CreateRuleTable
{
    const TABLE_NAME = 'amasty_groupassign_rule_table';

    /**
     * @var ProductMetadataInterface
     */
    private $metadata;

    public function __construct(ProductMetadataInterface $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $groupsTable = $setup->getTable(Group::ENTITY);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Customer Group Auto Assign rules table'
            )->addColumn(
                RuleInterface::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Id'
            )->addColumn(
                RuleInterface::RULE_NAME,
                Table::TYPE_TEXT,
                225,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Rule Name'
            )->addColumn(
                RuleInterface::CONDITIONS_SERIALIZED,
                Table::TYPE_TEXT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Serialized Conditions'
            )->addColumn(
                RuleInterface::MOVE_TO_GROUP,
                version_compare($this->metadata->getVersion(), '2.2.0', '<')
                    ? Table::TYPE_SMALLINT
                    : Table::TYPE_INTEGER,
                version_compare($this->metadata->getVersion(), '2.2.0', '<')
                    ? 5
                    : 10,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Destination group id'
            )->addColumn(
                RuleInterface::PRIORITY,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Priority of rul'
            )->addColumn(
                RuleInterface::STATUS,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Status'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    RuleInterface::MOVE_TO_GROUP,
                    $groupsTable,
                    'customer_group_id'
                ),
                RuleInterface::MOVE_TO_GROUP,
                $groupsTable,
                'customer_group_id',
                Table::ACTION_CASCADE
            );
    }
}
