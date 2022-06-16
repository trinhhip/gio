<?php


namespace OmnyfyCustomzation\Vendor\Setup;


use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Omnyfy\Vendor\Model\Vendor;

class UpgradeData implements UpgradeDataInterface
{
    protected $vendorSetupFactory;


    public function __construct(
        \Omnyfy\Vendor\Setup\VendorSetupFactory $vendorSetupFactory
    )
    {
        $this->vendorSetupFactory = $vendorSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $vendorSetup = $this->vendorSetupFactory->create(['setup' => $setup]);
        $version = $context->getVersion();
        if (version_compare($version, '1.0.1', '<')) {
            $vendorSetup->addAttribute(
                Vendor::ENTITY,
                'url_key',
                [
                    'type' => 'varchar',
                    'label' => 'Url Key',
                    'input' => 'text',
                    'required' => false,
                    'system' => false,
                ]
            );
        }
        if (version_compare($version, '1.0.2', '<')){
            $this->createVendorBankingInfo($vendorSetup);
        }
        if (version_compare($version, '1.0.3', '<')){
            $this->hideVendor($vendorSetup);
        }
        $setup->endSetup();
    }

    private function createVendorBankingInfo($vendorSetup){
        $bankingInfo = [
            'bank_name' => 'Bank Name',
            'bank_address' => 'Bank Address',
            'bank_swift' => 'SWIFT',
            'bank_account_number' => 'Account Number',
            'bank_account_name' => 'Account Name',
        ];
        foreach ($bankingInfo as $code => $label){
            $vendorSetup->addAttribute(
                Vendor::ENTITY,
                $code,
                [
                    'type' => 'varchar',
                    'label' => $label,
                    'input' => 'text',
                    'required' => false,
                    'system' => false,
                ]
            );
        }
    }
    private function hideVendor($vendorSetup){
        $vendorSetup->addAttribute(
            Vendor::ENTITY,
            'hide_vendor',
            [
                'type' => 'int',
                'label' => 'Hide Vendor',
                'input' => 'select',
                'required' => false,
                'system' => false,
            ]
        );
    }
}
