<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Setup\Operation;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo230
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $consentLogTable = $setup->getTable(CreateConsentLogTable::TABLE_NAME);
        //guests now can be logged. dropping FK with customer_entity
        foreach ($setup->getConnection()->getForeignKeys($consentLogTable) as $foreignKey) {
            $setup->getConnection()->dropForeignKey(
                $consentLogTable,
                $foreignKey['FK_NAME']
            );
        }
        $setup->getConnection()->addColumn(
            $consentLogTable,
            WithConsentInterface::LOGGED_EMAIL,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Logged Email',
                'nullable' => true
            ]
        );
    }
}
