<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Stripe\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddStripePayoutType implements DataPatchInterface
{
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }


    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $connection = $setup->getConnection();
        $table = $connection->getTableName('omnyfy_mcm_payout_type');
        if ($connection->isTableExists($table)) {
            $query = $connection->select()->from($table, ['payout_type'])->where("payout_type = ?", "Stripe");
            $result = $connection->fetchCol($query);
            if(empty($result)){
                $connection->insert($table, ['payout_type' => "Stripe"]);
            }
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
