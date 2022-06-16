<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


declare(strict_types=1);

namespace Amasty\Groupcat\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo158
{
    protected $targetTables = [
        'amasty_groupcat_rule_product',
        'amasty_groupcat_rule'
    ];

    protected $columnsToDrop = [
        'stock_status',
        'price_range_enabled',
        'by_price',
        'from_price',
        'to_price',
    ];

    public function execute(SchemaSetupInterface $setup)
    {
        foreach ($this->targetTables as $table) {
            $targetTableName = $setup->getTable($table);

            foreach ($this->columnsToDrop as $columnName) {
                $setup->getConnection()->dropColumn($targetTableName, $columnName);
            }
        }
    }
}
