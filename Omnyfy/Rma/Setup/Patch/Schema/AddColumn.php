<?php

/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\Rma\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddColumn implements SchemaPatchInterface
{
    protected $schemaSetup;


    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply()
    {
        $this->schemaSetup->getConnection()->addColumn(
            'mst_rma_item',
            'vendor_id',
            [
                'type'      => Table::TYPE_INTEGER,
                'nullable'  => false,
                'comment'   => 'Vendor Id',
                'unsigned'  => true,
                'after'     => 'order_item_id'
            ]
        );

        $this->schemaSetup->getConnection()->addColumn(
            'mst_rma_return_address',
            'vendor_id',
            [
                'type'      => Table::TYPE_INTEGER,
                'nullable'  => false,
                'comment'   => 'Vendor Id',
                'unsigned'  => true
            ]
        );
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
