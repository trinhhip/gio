<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup\Operation;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo200
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $deleteRequestTable = $setup->getTable(CreateDeleteRequestTable::TABLE_NAME);
        $consentLogTable = $setup->getTable(CreateConsentLogTable::TABLE_NAME);

        $setup->getConnection()->addColumn(
            $deleteRequestTable,
            DeleteRequestInterface::GOT_FROM,
            [
                'type' => Table::TYPE_TEXT,
                'size' => 20,
                'default' => null,
                'nullable' => true,
                'comment' => 'Initiator of deletion'
            ]
        );

        $setup->getConnection()->addColumn(
            $deleteRequestTable,
            DeleteRequestInterface::APPROVED,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Approved'
            ]
        );

        $setup->getConnection()->addColumn(
            $consentLogTable,
            WithConsentInterface::ACTION,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => true,
                'default' => 1,
                'comment' => 'Customer Action'
            ]
        );

        $setup->getConnection()->addColumn(
            $consentLogTable,
            WithConsentInterface::CONSENT_CODE,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Consent Code'
            ]
        );
    }
}
