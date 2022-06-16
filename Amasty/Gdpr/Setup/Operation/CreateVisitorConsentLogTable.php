<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup\Operation;

use Amasty\Gdpr\Model\VisitorConsentLog\VisitorConsentLog;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateVisitorConsentLogTable
{
    const TABLE_NAME = 'amasty_gdpr_visitor_consent_log';

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
        $customerTable = $setup->getTable('customer_entity');
        $websiteTable = $setup->getTable('store_website');

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty GDPR Table consent visitors'
            )->addColumn(
                VisitorConsentLog::ID,
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
                VisitorConsentLog::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Customer Id'
            )->addColumn(
                VisitorConsentLog::SESSION_ID,
                Table::TYPE_TEXT,
                64,
                [
                    'nullable' => true
                ],
                'Session Id'
            )->addColumn(
                VisitorConsentLog::DATE_CONSENTED,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ],
                'Date of consent'
            )->addColumn(
                VisitorConsentLog::POLICY_VERSION,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Policy Version'
            )->addColumn(
                VisitorConsentLog::WEBSITE_ID,
                Table::TYPE_SMALLINT,
                255,
                [
                    'unsigned' => true,
                    'nullable' => true,
                ],
                'Website ID'
            )->addColumn(
                VisitorConsentLog::IP,
                Table::TYPE_TEXT,
                127,
                [
                    'nullable' => true,
                ],
                'Remote IP Address'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    VisitorConsentLog::CUSTOMER_ID,
                    $customerTable,
                    'entity_id'
                ),
                VisitorConsentLog::CUSTOMER_ID,
                $customerTable,
                'entity_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    VisitorConsentLog::WEBSITE_ID,
                    $websiteTable,
                    'website_id'
                ),
                VisitorConsentLog::WEBSITE_ID,
                $websiteTable,
                'website_id',
                Table::ACTION_SET_NULL
            );
    }
}
