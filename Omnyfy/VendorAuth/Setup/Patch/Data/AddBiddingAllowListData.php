<?php
declare(strict_types=1);

namespace Omnyfy\VendorAuth\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddBiddingAllowListData implements DataPatchInterface
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
            $endpoints = [
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/bidding/milestones',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/bidding/milestones/{{milestone_entity_id}}',
                    'method' => 'PUT'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/bidding/termsandconditions',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/bidding/accepttermsandconditions',
                    'method' => 'PUT'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/bidding/rates',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/bidding/configurations',
                    'method' => 'GET'
                ]
            ];

            foreach ($endpoints as $endpoint) {
                $conn->insert($allowlistTable, $endpoint);
            }
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
