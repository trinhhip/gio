<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Setup\Operation;

use Amasty\Gdpr\Api\Data\ActionLogInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo250
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $actionLogTable = $setup->getTable(CreateActionLogTable::TABLE_NAME);

        $setup->getConnection()->addColumn(
            $actionLogTable,
            ActionLogInterface::COMMENT,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Action Comment',
                'nullable' => true
            ]
        );
    }
}
