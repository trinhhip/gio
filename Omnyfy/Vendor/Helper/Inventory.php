<?php
/**
 * Project: Inventory
 * User: Ryan
 * Date: 04/06/2021 
 * Time: 9:10 AM
 */
namespace Omnyfy\Vendor\Helper;

class Inventory extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $resourceConnection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resourceConnection = $resourceConnection;
    }

    public function getSourceCodeStockIdSku($inventoryId) {
        if (empty($inventoryId)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $inventoryTable = $conn->getTableName('omnyfy_vendor_inventory');
        $queryInventory = $conn->select()->from($inventoryTable, ['source_stock_id', 'sku'])->where("inventory_id = $inventoryId");
        $data = $conn->fetchRow($queryInventory);
        $result = [];

        if ($data) {
            $result['sku'] = $data['sku'];
            $sourceStockTable = $conn->getTableName('omnyfy_vendor_source_stock');
            $query = $conn->select()->from($sourceStockTable, ['stock_id', 'source_code'])->where("id = ?", $data['source_stock_id']);
            $sourceStockData = $conn->fetchRow($query);
            if (!empty($sourceStockData)) {
                $result['stock_id'] = $sourceStockData['stock_id'];
                $result['source_code'] = $sourceStockData['source_code'];
            }
        }

        return $result;
    }

    public function getResourceConnection() {
        return $this->resourceConnection;
    }
}