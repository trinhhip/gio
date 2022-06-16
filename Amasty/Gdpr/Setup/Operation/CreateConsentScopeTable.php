<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Setup\Operation;

use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\Store;

class CreateConsentScopeTable
{
    const TABLE_NAME = 'amasty_gdpr_consents_scope';

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
    public function createTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(self::TABLE_NAME);
        $table = $setup->getConnection()->newTable(
            $tableName
        )->setComment(
            'Amasty GDPR. Consent settings by store view'
        )->addColumn(
            ConsentStore::ID,
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ],
            'Table ID'
        )->addColumn(
            ConsentStore::CONSENT_STORE_ID,
            Table::TYPE_SMALLINT,
            null,
            [
                'nullable' => false,
                'unsigned' => true
            ],
            'Store id relation'
        )->addColumn(
            ConsentStore::CONSENT_ENTITY_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false,
                'unsigned' => true
            ],
            'Consent entity id'
        )->addColumn(
            ConsentStore::IS_ENABLED,
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => true
            ],
            'Is consent enabled'
        )->addColumn(
            ConsentStore::IS_REQUIRED,
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => true
            ]
        )->addColumn(
            ConsentStore::LOG_THE_CONSENT,
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => true
            ],
            'Customer’s Consent will be saved to the ‘Consent Log’ grid if enabled'
        )->addColumn(
            ConsentStore::HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT,
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => true
            ],
            'The system will not display a Consent to customers if they left their consents'
        )->addColumn(
            ConsentStore::CONSENT_LOCATION,
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => true
            ],
            'Serialized conditions where to show consent consents'
        )->addColumn(
            ConsentStore::LINK_TYPE,
            Table::TYPE_SMALLINT,
            2,
            [
                'nullable' => true
            ],
            'Link type'
        )->addColumn(
            ConsentStore::CMS_PAGE_ID,
            Table::TYPE_SMALLINT,
            6,
            [
                'nullable' => true
            ],
            'Cms page ID'
        )->addColumn(
            ConsentStore::CONSENT_TEXT,
            Table::TYPE_TEXT,
            null,
            [
                'nullable' => true
            ],
            'Consent text'
        )->addColumn(
            ConsentStore::COUNTRIES,
            Table::TYPE_TEXT,
            null,
            [
                'nullable' => true
            ],
            'Show for specific countries'
        )->addColumn(
            ConsentStore::VISIBILITY,
            Table::TYPE_SMALLINT,
            null,
            [
                'nullable' => true
            ],
            'Regions where consents would be visible'
        )->addColumn(
            ConsentStore::SORT_ORDER,
            Table::TYPE_SMALLINT,
            10,
            [
                'unsigned' => true,
                'nullable' => true
            ],
            'Sort order'
        );
        $this->addForeignKeys($setup, $table);
        $this->addIndexes($setup, $table, $tableName);

        return $table;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param Table $table
     *
     * @throws \Zend_Db_Exception
     */
    private function addForeignKeys(SchemaSetupInterface $setup, Table $table)
    {
        $storeTable = $setup->getTable(Store::ENTITY);
        $consentTable = $setup->getTable(CreateConsentsTable::TABLE_NAME);
        $cmsTable = $setup->getTable('cms_page');

        $table->addForeignKey(
            $setup->getFkName(
                $table->getName(),
                ConsentStore::CONSENT_STORE_ID,
                $storeTable,
                Store::STORE_ID
            ),
            ConsentStore::CONSENT_STORE_ID,
            $storeTable,
            Store::STORE_ID,
            Table::ACTION_CASCADE
        );

        $table->addForeignKey(
            $setup->getFkName(
                $table->getName(),
                ConsentStore::CMS_PAGE_ID,
                $cmsTable,
                PageInterface::PAGE_ID
            ),
            ConsentStore::CMS_PAGE_ID,
            $cmsTable,
            PageInterface::PAGE_ID,
            Table::ACTION_SET_NULL
        );

        $table->addForeignKey(
            $setup->getFkName(
                $table->getName(),
                ConsentStore::CONSENT_ENTITY_ID,
                CreateConsentsTable::TABLE_NAME,
                Consent::ID
            ),
            ConsentStore::CONSENT_ENTITY_ID,
            $consentTable,
            Consent::ID,
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param Table $table
     * @param string $tableName
     *
     * @throws \Zend_Db_Exception
     */
    private function addIndexes(SchemaSetupInterface $setup, Table $table, string $tableName)
    {
        $table->addIndex(
            $setup->getIdxName(
                $tableName,
                [ConsentStore::CONSENT_ENTITY_ID, ConsentStore::CONSENT_STORE_ID]
            ),
            [ConsentStore::CONSENT_ENTITY_ID, ConsentStore::CONSENT_STORE_ID]
        );
    }
}
