<?php
namespace Omnyfy\VendorAuth\Setup\Patch\Data;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddShipOrderAllowList implements DataPatchInterface
{
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface */
    protected $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public function apply(){
        $this->moduleDataSetup->startSetup();
        $conn = $this->moduleDataSetup->getConnection();

        $tableName = $conn->getTableName('omnyfy_vendorauth_endpoint_allowlist');

        $endpoints = [
            [
                'store_id' => 1,
                'vendor_id' => 0,
                'endpoint_type' => 'REST API',
                'endpoint' => 'V1/order/{id}/ship',
                'method' => 'POST'
            ]
        ];
        
        $conn->insertArray(
            $this->moduleDataSetup->getTable('omnyfy_vendorauth_endpoint_allowlist'),
            ['store_id', 'vendor_id', 'endpoint_type', 'endpoint', 'method'],
            $endpoints
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
