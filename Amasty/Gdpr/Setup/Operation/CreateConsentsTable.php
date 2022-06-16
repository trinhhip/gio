<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Setup\Operation;

use Amasty\Gdpr\Model\Consent\Consent;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateConsentsTable
{
    const TABLE_NAME = 'amasty_gdpr_consents';

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
        $tableName = $setup->getTable(self::TABLE_NAME);
        $table = $setup->getConnection()->newTable(
            $tableName
        )->setComment(
            'Amasty GDPR. Consent settings'
        )->addColumn(
            Consent::ID,
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ]
        )->addColumn(
            Consent::CONSENT_NAME,
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
                'default'  => ''
            ]
        )->addColumn(
            Consent::CONSENT_CODE,
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => false
            ]
        );
        $this->addIndexes($setup, $table);

        return $table;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param Table $table
     *
     * @throws \Zend_Db_Exception
     */
    private function addIndexes(SchemaSetupInterface $setup, Table $table)
    {
        $table->addIndex(
            $setup->getIdxName(
                self::TABLE_NAME,
                [Consent::CONSENT_CODE],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            Consent::CONSENT_CODE,
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
    }
}
