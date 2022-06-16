<?php

/**
 * Project: Multi Vendors.
 * User: jing
 * Date: 29/1/18
 * Time: 5:32 PM
 */

namespace Omnyfy\Vendor\Model\Resource;

use Laminas\Http\Header\IfRange;

class Inventory extends \Omnyfy\Core\Model\ResourceModel\AbstractDbModel
{
    protected $sourceItemsProcessorFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\InventoryCatalogApi\Model\SourceItemsProcessorInterfaceFactory $sourceItemsProcessorFactory,
        $connectionName = null
    ) {
        $this->transactionManager = $context->getTransactionManager();
        $this->_resources = $context->getResources();
        $this->objectRelationProcessor = $context->getObjectRelationProcessor();
        if ($connectionName !== null) {
            $this->connectionName = $connectionName;
        }
        $this->sourceItemsProcessorFactory = $sourceItemsProcessorFactory;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('omnyfy_vendor_inventory', 'inventory_id');
    }

    protected function getUpdateFields()
    {
        return [
            'quantity',
        ];
    }

    public function addProductIdsToLocation($productIds, $locationId, $defaultQty = 0)
    {
        if (empty($productIds) || empty($locationId)) {
            return;
        }

        $conn = $this->getConnection();

        $productTable = $conn->getTableName('catalog_product_entity');
        $zendDbExprNull = new \Zend_Db_Expr('NULL');
        $select = $conn->select()->from(
            $productTable,
            [
                'inventory_id' => $zendDbExprNull,
                'product_id' => 'entity_id',
                'source_stock_id' => new \Zend_Db_Expr($locationId),
                'quantity' => new \Zend_Db_Expr(intval($defaultQty))
            ]
        )
            ->where('entity_id IN (?)', $productIds);
        $insertQuery = $conn->insertFromSelect(
            $select,
            $this->getMainTable(),
            [
                'inventory_id', 'product_id', 'source_stock_id', 'quantity'
            ],
            \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_IGNORE
        );

        $conn->query($insertQuery);
    }

    public function removeByVendorIds($productIds, $vendorIds)
    {
        if (empty($productIds) || empty($vendorIds)) {
            return;
        }

        $conn = $this->getConnection();

        $locationTable = $conn->getTableName('omnyfy_vendor_location_entity');

        $conn->delete(
            $this->getMainTable(),
            [
                'product_id IN (?)' => $productIds,
                'location_id IN (SELECT entity_id FROM ' . $locationTable . ' WHERE vendor_id IN (?))' => $vendorIds,
            ]
        );
    }

    public function removeByNotInVendorIds($productIds, $vendorIds)
    {
        if (empty($productIds) || empty($vendorIds)) {
            return;
        }

        $conn = $this->getConnection();

        $locationTable = $conn->getTableName('omnyfy_vendor_location_entity');

        $conn->delete(
            $this->getMainTable(),
            [
                'product_id IN (?)' => $productIds,
                'location_id IN (SELECT entity_id FROM ' . $locationTable . ' WHERE vendor_id NOT IN (?))' => $vendorIds,
            ]
        );
    }

    public function updateQty($inventoryId, $qty)
    {
        if (empty($inventoryId)) {
            return;
        }

        if(empty($qty)){
            $qty = 0;
        }

        $conn = $this->getConnection();

        $conn->update(
            $this->getMainTable(),
            ['qty' => $qty],
            ['inventory_id=?' => $inventoryId]
        );
    }

    public function loadInventoryGroupedByLocation($productId, $websiteId, &$vendorId, $activeVendorOnly = false, $activeLocationOnly = false)
    {
        if (!is_array($productId)) {
            $productId = [$productId];
        }

        $websiteIds = [];
        if (!empty($websiteId)) {
            $websiteIds[] = $websiteId;
        }

        $conn = $this->getConnection();

        $inventoryTable = $this->getMainTable();

        // $locationTable = $this->getTable('omnyfy_vendor_location_entity');

        $vendorTable = $this->getTable('omnyfy_vendor_vendor_entity');

        $profileTable = $this->getTable('omnyfy_vendor_profile');

        $sourceTable = $this->getTable('inventory_source');

        $stockTable = $this->getTable('inventory_stock');

        $sourceStockTable = $this->getTable('omnyfy_vendor_source_stock');

        $linkTable = $this->getTable('inventory_source_stock_link');

        //profile location table
        // $profileLocationTable = $this->getTable('omnyfy_vendor_profile_location');

        $vendorStatusCondition = $activeVendorOnly ? ' AND v.status=' . \Omnyfy\Vendor\Api\Data\VendorInterface::STATUS_ENABLED : '';

        // $locationStatusCondition = $activeLocationOnly ? ' AND l.status=' . \Omnyfy\Vendor\Api\Data\LocationInterface::STATUS_ENABLED : '';

        $select = $conn->select()
            ->from(['i' => $inventoryTable])
            ->join(
                ['source_stock' => $sourceStockTable],
                'source_stock.id = i.source_stock_id'
            )
            ->join(
                ['is' => $sourceTable],
                'source_stock.source_code = is.source_code AND is.enabled= 1'
            )
            ->join(
                ['ist' => $stockTable],
                'source_stock.stock_id = ist.stock_id'
            )
            ->join(
                ['link' => $linkTable],
                'link.source_code = source_stock.source_code AND link.stock_id = source_stock.stock_id'
            )
            ->join(
                ['v' => $vendorTable],
                'source_stock.vendor_id=v.entity_id' . $vendorStatusCondition,
                ['vendor_id' => 'source_stock.vendor_id']
            )
            ->join(
                ['p' => $profileTable],
                'p.vendor_id=v.entity_id',
                []
            )
            ->where('i.product_id IN (?)', $productId);

        if (!empty($websiteIds)) {
            $select->where('p.website_id IN (?)', $websiteIds);
        }

        $select->order('priority');

        $result = [];
        $dataSet = $conn->fetchAll($select);
        foreach ($dataSet as $raw) {
            $result[$raw['source_stock_id']] = $raw['quantity'];
            $vendorId = $raw['vendor_id'];
        }

        return $result;
    }

    public function loadQtysByProductIds($productIds, $websiteId, $activeVendorOnly = false, $activeLocationOnly = false)
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }

        $websiteIds = [];
        if (!empty($websiteId)) {
            $websiteIds[] = $websiteId;
        }

        $conn = $this->getConnection();

        $inventoryTable = $this->getMainTable();

        $locationTable = $this->getTable('omnyfy_vendor_location_entity');

        $vendorTable = $this->getTable('omnyfy_vendor_vendor_entity');

        $profileTable = $this->getTable('omnyfy_vendor_profile');

        //profile location table
        $profileLocationTable = $this->getTable('omnyfy_vendor_profile_location');

        $vendorStatusCondition = $activeVendorOnly ? ' AND v.status=' . \Omnyfy\Vendor\Api\Data\VendorInterface::STATUS_ENABLED : '';

        $locationStatusCondition = $activeLocationOnly ? ' AND l.status=' . \Omnyfy\Vendor\Api\Data\LocationInterface::STATUS_ENABLED : '';

        $select = $conn->select()
            ->from(['i' => $inventoryTable])
            ->join(
                ['l' => $locationTable],
                'l.entity_id=i.location_id' . $locationStatusCondition,
                ['priority' => 'l.priority']
            )
            ->join(
                ['v' => $vendorTable],
                'l.vendor_id=v.entity_id' . $vendorStatusCondition,
                ['vendor_id' => 'l.vendor_id']
            )
            ->join(
                ['p' => $profileTable],
                'p.vendor_id=v.entity_id',
                []
            )
            ->join(
                ['pl' => $profileLocationTable],
                'pl.location_id=l.entity_id AND pl.profile_id=p.profile_id',
                []
            )
            ->where('i.product_id IN (?)', $productIds);

        if (!empty($websiteIds)) {
            $select->where('p.website_id IN (?)', $websiteIds);
        }

        $select->order(['product_id', 'priority']);

        $result = [];
        $dataSet = $conn->fetchAll($select);
        foreach ($dataSet as $raw) {
            $productId = $raw['product_id'];
            if (!array_key_exists($productId, $result)) {
                $result[$productId] = [];
            }
            $result[$productId][$raw['location_id']] = $raw['qty'];
        }

        return $result;
    }

    public function getSourceStockIdsBySku($sku)
    {
        if (empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'source_stock_id')->where("sku =?", $sku);
        $result = $conn->fetchCol($query);

        return $result;
    }

    public function saveData($data)
    {
        if (empty($data)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable())
            ->where('source_stock_id = ?', $data['source_stock_id'])
            ->where('sku = ?', $data['sku']);
        $row = $conn->fetchAll($query);
        if (empty($row)) {
            $conn->insert($this->getMainTable(), $data);
        }
    }

    public function saveNewData($data)
    {
        if (empty($data)) {
            return;
        }

        $conn = $this->getConnection();
        $conn->insert($this->getMainTable(), $data);
    }

    public function getOldQty($id, $sku)
    {
        if (empty($id) || empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'quantity')
            ->where('source_stock_id = ?', $id)
            ->where('sku = ?', $sku);
        $raw = $conn->fetchCol($query);

        if (!empty($raw)) {
            return $raw[0];
        } else {
            return 0;
        }
    }

    public function removeBySourceCode($sourceCode, $sku)
    {
        if (empty($sourceCode) || empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $conn->delete($this->getMainTable(), 'source_code = "'.$sourceCode.'" AND sku = "'.$sku.'"');
    }

    public function updateQtyBySourceCode($sourceCode, $sku, $productId, $sourceStockId, $quantity)
    {
        if (empty($sourceCode) || empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $conn->delete($this->getMainTable(), ['source_code = ?' => $sourceCode, 'product_id = ?' => $productId]);
        $conn->insert($this->getMainTable(), ['product_id' => $productId, 'sku' => $sku, 'source_stock_id' => $sourceStockId, 'quantity' => $quantity, 'source_code' => $sourceCode]);
    }

    public function saveDuplicateData($data)
    {
        if (empty($data)) {
            return;
        }

        $conn = $this->getConnection();
        $sourceCode = $data['source_code'];
        $productId = $data['product_id'];
        $conn->delete($this->getMainTable(), ['source_code = ?' => $sourceCode, 'product_id = ?' => $productId]);
        $conn->insertOnDuplicate($this->getMainTable(), $data);
    }

    public function getSourceCodeBySku($sku)
    {
        if (empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'source_code')->where('sku = ?', $sku);
        $result = $conn->fetchCol($query);
        $result = array_unique($result);

        return $result;
    }

    public function isAssigned($sourceStockId, $sku)
    {
        if (empty($sourceStockId) || empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'inventory_id')
            ->where('source_stock_id = ?', $sourceStockId)
            ->where('sku = ?', $sku);
        $row = $conn->fetchCol($query);

        if (!empty($row)) {
            return true;
        } else {
            return false;
        }
    }

    public function getSourceStockIdsBySourceCodeSku($sourceCode, $sku)
    {
        if (empty($sourceCode) || empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'source_stock_id')->where('source_code = ?', $sourceCode)->where('sku = ?', $sku);
        $result = $conn->fetchCol($query);

        return $result;
    }

    public function isProductAssigned($sourceCode, $sku)
    {
        if (empty($sourceCode) || empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'inventory_id')->where('source_code = ?', $sourceCode)->where('sku = ?', $sku);
        $result = $conn->fetchCol($query);

        return (!empty($result)) ? true : false;
    }

    public function getQty($sourceCode, $sku)
    {
        if (empty($sourceCode) || empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'quantity')
            ->where('source_code = ?', $sourceCode)
            ->where('sku = ?', $sku);
        $raw = $conn->fetchCol($query);
        $result = 0;
        if (!empty($raw)) {
            $result = $raw[0];
        }

        return $result;
    }

    public function isProductAssignedToSourceStock($sourceStockId, $sku)
    {
        if (empty($sourceStockId) || empty($sku)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'inventory_id')
            ->where('source_stock_id = ?', $sourceStockId)
            ->where('sku = ?', $sku);
        $result = $conn->fetchOne($query);

        return (empty($result)) ? false : true;
    }

    public function isNoChildProduct($parentProductId, $sourceCode)
    {
        if (empty($parentProductId) || (empty($sourceCode))) {
            return;
        }

        $conn = $this->getConnection();
        $linkProductTable = 'catalog_product_super_link';
        $selectChildIds = $conn->select()->from($linkProductTable, 'product_id')->where("parent_id = $parentProductId");
        $childIds = $conn->fetchCol($selectChildIds);
        $isOutOfChildProducts = true;
        foreach ($childIds as $childId) {
            $selectIsExistsChildId = $conn->select()->from($this->getMainTable(), 'product_id')->where('product_id = ?', $childId)->where('source_code = ?', $sourceCode);
            $result = $conn->fetchCol($selectIsExistsChildId);
            if (!empty($result)) {
                $isOutOfChildProducts = false;
                break;
            }
        }

        return $isOutOfChildProducts;
    }

    public function removeByProducIdAndSourceCode($productId, $sourceCode) {
        if (empty($productId) || empty($sourceCode)) {
            return;
        }

        $conn = $this->getConnection();
        $conn->delete($this->getMainTable(), 'product_id = '.$productId.' AND source_code = "'.$sourceCode.'"');
    }

    public function isParentProductExists($parentId, $sourceCode) {
        if (empty($parentId) || empty($sourceCode)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'inventory_id')->where('product_id = ?', $parentId)->where('source_code = ?', $sourceCode);
        $result = $conn->fetchCol($query);

        return empty($result) ? false : true;
    }

    public function importSave($data, $dataSourceItem) {
        if (empty($data) || empty($dataSourceItem)) {
            return;
        }
        $conn = $this->getConnection();
        $sourceItemsProcessor = $this->sourceItemsProcessorFactory->create();
        if (!$this->isProductAssigned($data['source_code'], $data['sku'])) {
            if (!is_array($data['source_stock_id'])) {
                $conn->insertOnDuplicate(
                    $this->getMainTable(),
                    $data,
                    $this->getUpdateFields()
                );
                $sourceItemsProcessor->execute($data['sku'], $dataSourceItem);
            } else {
                foreach ($data['source_stock_id'] as $id) {
                    $dataSave = [
                        'inventory_id' => $data['inventory_id'],
                        'product_id' => $data['product_id'],
                        'source_code' => $data['source_code'],
                        'quantity' => $data['quantity'],
                        'source_stock_id' => $id
                    ];
                    $conn->insertOnDuplicate(
                        $this->getMainTable(),
                        $dataSave,
                        $this->getUpdateFields()
                    );
                }
                $sourceItemsProcessor->execute($data['sku'], $dataSourceItem);
            }
        } else {
            $sourceCode = $data['source_code'];
            $sku = $data['sku'];
            $conn->update($conn->getTableName('omnyfy_vendor_inventory'), ['quantity' => $data['quantity']], 'source_code = "'.$sourceCode.'" AND sku = "'.$sku.'"');
            $sourceItemsProcessor->execute($data['sku'], $dataSourceItem);
        }
    }

    public function returnQty(array $inventoryData) {
        if (empty($inventoryData['product_id']) || empty($inventoryData['source_code']) || empty($inventoryData['qty'])) {
            return;
        }

       $conn = $this->getConnection();
       $returnOmnyfyInventory = 'UPDATE omnyfy_vendor_inventory SET quantity = quantity + ' . $inventoryData['qty'] . ' WHERE sku = "' . $inventoryData['sku'] . '" AND source_code = "' . $inventoryData['source_code'] . '"';
       $returnCoreInventory = 'UPDATE inventory_source_item SET quantity = quantity + ' . $inventoryData['qty'] . ' WHERE sku = "' . $inventoryData['sku'] . '" AND source_code = "' . $inventoryData['source_code'] . '"';
       $conn->query($returnOmnyfyInventory);
       $conn->query($returnCoreInventory);
    }
}
