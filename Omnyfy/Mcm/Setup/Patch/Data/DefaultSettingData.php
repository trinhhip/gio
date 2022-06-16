<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Mcm\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Omnyfy\Mcm\Model\SequenceFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorBankAccountType;

class DefaultSettingData implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;
    private $sequenceFactory;
    private $vendorBankAccountType;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \Omnyfy\Mcm\Model\SequenceFactory $sequenceFactory,
        VendorBankAccountType $vendorBankAccountType

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->sequenceFactory = $sequenceFactory;
        $this->vendorBankAccountType = $vendorBankAccountType;
    }
    /**
     * @inheritdoc
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        // $eavSetup->updateAttribute(
        //     Category::ENTITY,
        //     'category_commission_percentage',
        //     [
        //         'note' => 'Set the commission rate that would like to charge Vendors for sales of products or services under this category. Do not enter the "%" sign simply enter the number (e.g. for 5% enter 5.00)'
        //     ]
        // );
        $setup->startSetup();
        $eavSetup->addAttribute(
            Category::ENTITY,
            'category_commission_percentage',
            [
                'type' => 'varchar',
                'label' => 'Category Commission Percentage',
                'input' => 'text',
                'required' => false,
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
            ]
        );
        if ($setup->tableExists('omnyfy_mcm_sequence')) {
            $payoutSequence = $this->createSequence()->load('payout_ref', 'type');
            if (empty($payoutSequence->getData())) {
                $sequenceData = [
                    'type' => 'payout_ref',
                    'prefix' => 'PR',
                    'last_value' => '0'
                ];
                $this->createSequence()->setData($sequenceData)->save();
            }
        }
        if ($setup->tableExists('omnyfy_mcm_vendor_bank_account_type')) {
            $bankAccTypeData = [
                ['account_type' => 'Bank Account (Direct Deposit)'],
                ['account_type' => 'International Bank Account (EFT)'],
            ];
            $this->vendorBankAccountType->insertMultiple('omnyfy_mcm_vendor_bank_account_type', $bankAccTypeData);
        }
        if ($setup->tableExists('omnyfy_mcm_sequence')) {
            $invoiceSequence = $this->createSequence()->load('invoice_ref', 'type');
            if (empty($invoiceSequence->getData())) {
                $sequenceData = [
                    'type' => 'invoice_ref',
                    'prefix' => 'INV',
                    'last_value' => '0'
                ];
                $this->createSequence()->setData($sequenceData)->save();
            }
        }

        $connection = $setup->getConnection();
        $express = new \Zend_Db_Expr('REPLACE(`path`, "marketplacesetting", "omnyfy_mcm")');
        $connection->update(
            $setup->getTable('core_config_data'),
            ['path' => $express],
            ['`path` LIKE ?' => 'marketplacesetting%']
        );

        $setup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function createSequence()
    {
        return $this->sequenceFactory->create();
    }
}
