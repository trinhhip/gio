<?php
namespace Omnyfy\ProductImport\Helper;

class ProductImport extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Inventory\Model\SourceFactory $sourceModelFactory
     */
    protected $sourceModelFactory;

    /**
     * @var \Omnyfy\Vendor\Model\Resource\Inventory
     */
    protected $inventoryResource;

    /**
     * @var \Omnyfy\Vendor\Model\Resource\Vendor
     */
    protected $vendorResource;

    /**
     * @var \Omnyfy\Vendor\Model\Resource\VendorSourceStock
     */
    protected $vendorSourceStock;

    /**
     * constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Inventory\Model\SourceFactory $sourceModelFactory
     * @param \Omnyfy\Vendor\Model\Resource\Inventory $inventoryResource
     * @param \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource
     * @param \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vendorSourceStock
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Inventory\Model\SourceFactory $sourceModelFactory,
        \Omnyfy\Vendor\Model\Resource\Inventory $inventoryResource,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vendorSourceStock
    ){
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->sourceModelFactory = $sourceModelFactory;
        $this->inventoryResource = $inventoryResource;
        $this->vendorResource = $vendorResource;
        $this->vendorSourceStock = $vendorSourceStock;
        parent::__construct($context);
    }

    public function updateVendorSourceInventory($product, $inventorySource)
    {
        $message = "";
        if (count($inventorySource) > 0) {
            $productIdToVendorId = [];
            $sourceModel = $this->sourceModelFactory->create();

            foreach ($inventorySource as $inventory) {
                if ($product->getSku() == $inventory['sku'] && count($inventory['vendor_ids']) > 0) {
                    foreach ($inventory['vendor_ids'] as $vendorId) {
                        $productIdToVendorId[] = [
                            'product_id' => $product->id,
                            'vendor_id' => $vendorId
                        ];
                    }
                    $this->vendorResource->saveProductRelation($productIdToVendorId);
                }

                if ($product->getSku() == $inventory['sku'] && count($inventory['inventory']) > 0) {
                    foreach ($inventory['inventory'] as $inv) {

                        if (isset($inv['source_code'])) {
                            $sourceCode = $inv['source_code'];
                            $sourceModel->load($sourceCode);
                            $sourceStockId = $this->getSourceStockIdByCode($sourceCode);

                            if (isset($sourceStockId)) {
                                $productIdsToSource = [
                                    'sku' => $inventory['sku'],
                                    'inventory_id' => new \Zend_Db_Expr('NULL'),
                                    'product_id' => $product->id,
                                    'source_code' => $sourceCode,
                                    'quantity' => $inv['qty'],
                                    'source_stock_id' => $this->getSourceStockIdByCode($sourceCode)
                                ];

                                $assignedSource[0] = [
                                    'source_code' => $sourceCode,
                                    'name' => $sourceModel->getName(),
                                    'quantity' => $inv['qty'],
                                    'source_status' => '1',
                                    'notify_stock_qty' => '1',
                                    'notify_stock_qty_use_default' => '1',
                                    'position' => '1',
                                    'status' => '1'
                                ];

                                $this->inventoryResource->importSave($productIdsToSource, $assignedSource);
                            }else{
                                $message = sprintf("Source code %s is not found", $sourceCode);
                            }
                        }else{
                            $message = 'Please assign source_code value';
                        }

                    }
                }
            }
        }
        return $message;
    }

    public function getInventoryByProductIdLocationId($productId, $locationId)
    {
        $connection = $this->resourceConnection->getConnection();
        $inventoryTable = $connection->getTableName('omnyfy_vendor_inventory');

        $query = "SELECT `inventory_id` FROM ". $inventoryTable .
            " WHERE product_id = " . $productId .
            " AND location_id = " . $locationId
        ;
        return $connection->fetchOne($query);
    }

    public function getVendorIdByLocationId($locationId)
    {
        $connection = $this->resourceConnection->getConnection();
        $locationTable = $connection->getTableName('omnyfy_vendor_location_entity');
        $query = "SELECT `vendor_id` FROM ". $locationTable .
            " WHERE entity_id = " . $locationId
        ;
        return $connection->fetchOne($query);
    }

    public function getConfigData($path)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $storeScope);
    }

    private function getSourceStockIdByCode($sourceCode)
    {
        $sourceStock = $this->vendorSourceStock->getIdsBySourceCode($sourceCode, true);
        return $sourceStock;
    }
}
