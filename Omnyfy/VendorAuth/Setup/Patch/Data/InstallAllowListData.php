<?php
declare(strict_types=1);

namespace Omnyfy\VendorAuth\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallAllowListData implements DataPatchInterface
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
                    'vendor_id' => 0, //0 is for all vendor
                    'endpoint_type' => 'REST API', //Possible values: REST API, GraphQL
                    'endpoint' => 'V1/vendor/profile',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/locations',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/vendor/storefront',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/vendor/storefront',
                    'method' => 'PUT'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/products',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/products/:sku',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/orders',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/orders/{id}',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/orders/items',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/orders/items/{id}',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/shipments',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/shipments/{id}',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/omnyfy/products',
                    'method' => 'POST'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/omnyfy/products',
                    'method' => 'PUT'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/shipment',
                    'method' => 'GET'
                ],
                [
                    'store_id' => 1,
                    'vendor_id' => 0,
                    'endpoint_type' => 'REST API',
                    'endpoint' => 'V1/categories/list',
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
