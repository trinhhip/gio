<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Stripe\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddEavAttribute implements DataPatchInterface
{
    private $moduleDataSetup;
    protected $vendorSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Omnyfy\Vendor\Setup\VendorSetupFactory $vendorSetupFactory

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->vendorSetupFactory = $vendorSetupFactory;
    }
    /**
     * @inheritdoc
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $vendorSetup = $this->vendorSetupFactory->create(['setup' => $setup]);

        $vendorEntity = \Omnyfy\Vendor\Model\Vendor::ENTITY;

        $vendorSetup->addAttribute(
            $vendorEntity,
            'stripe_account_code',
            [
                'type' => 'varchar',
                'label' => 'Stripe Account Code',
                'input' => 'text',
                'system' => false,
                'required' => false,
                'is_visible' => false
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
