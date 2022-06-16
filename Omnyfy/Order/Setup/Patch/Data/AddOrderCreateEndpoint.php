<?php
declare(strict_types=1);

namespace Omnyfy\Order\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddOrderCreateEndpoint implements DataPatchInterface
{
    protected $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $conn = $this->moduleDataSetup->getConnection();
        $allowlistTable = $conn->getTableName("omnyfy_vendorauth_endpoint_allowlist");

        if ($conn->isTableExists($allowlistTable)) {
            $endpoint = [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/orders',
                    'method' => 'POST'
                ]
            ;
            $conn->insert($allowlistTable, $endpoint);
        }

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
