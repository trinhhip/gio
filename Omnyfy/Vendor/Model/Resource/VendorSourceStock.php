<?php
namespace Omnyfy\Vendor\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;

class VendorSourceStock extends AbstractDb implements  IdentityInterface{

    const CACHE_TAG = 'omnyfy_vendor_source_stock';
    protected $_cacheTag = 'omnyfy_vendor_source_stock';
    protected $_eventPrefix = 'omnyfy_vendor_source_stock';

    protected function _construct()
    {
        $this->_init('omnyfy_vendor_source_stock', 'id');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getVendorIdsBySourceStockIds($sourceStockIds) {
        if (empty($sourceStockIds)) {
            return [];
        }

        $conn = $this->getConnection();
        $table = $this->getMainTable();
        $select = $conn->select()->from(
            $table,
            ['id', 'vendor_id']
        )
            ->where(
                "id IN (?)",
                $sourceStockIds
            );

        $rows = $conn->fetchAll($select);
        $result = [];
        foreach ($rows as $row) {
            $sourceStockId = $row['id'];
            $result[$sourceStockId] = $row['vendor_id'];
        }

        return $result;
    }

    public function getIdsBySourceCode($sourceCode, $isOnly = false) {
        $conn = $this->getConnection();
        $table = $this->getMainTable();
        $select = $conn->select()->from(
            $table,
            ['id']
        )
        ->joinInner('inventory_source_stock_link', 
                    "inventory_source_stock_link.source_code = omnyfy_vendor_source_stock.source_code AND inventory_source_stock_link.stock_id = omnyfy_vendor_source_stock.stock_id",
                    'priority'
        )
        ->where("omnyfy_vendor_source_stock.source_code = ?", $sourceCode)
        ->order('priority DESC');

        $rows = $conn->fetchAll($select);
        $result = [];
        foreach ($rows as $row) {
            $result[] =$row['id'];
        }

        if ($isOnly) {
            if (!empty($result)) {
                return $result[0];
            } else {
                return null;
            }
        }

        return $result;
    }

    public function getStockIdSourceCode($id) {
        if (empty($id)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), ['stock_id'])->where("id = $id");
        $result = $conn->fetchCol($query);
        $stockId = null;
        if (!empty($result)) {
            $stockId = $result[0];
        }

        return $stockId;
    } 
    
    public function getSourceCodeById($id) {
        if (empty($id)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'source_code')->where("id = $id");
        $raw = $conn->fetchCol($query);

        if (!empty($raw)) {
            return $raw[0];
        }

        return null;
    }   

    public function saveData($dataSave) {
        if (empty($dataSave)) {
            return;
        }

        $conn = $this->getConnection();
        $conn->insert($this->getMainTable(), $dataSave);
    }

    public function getStockIdBySourceStockId($id) {
        if (empty($id)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'stock_id')->where('id = ?', $id);
        $result = $conn->fetchOne($query);

        return $result;
    }

    public function isSourceAssignedToStock($sourceCode, $stockId) {
        if (empty($sourceCode) || empty($stockId)) {
            return;
        }

        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'id')->where('source_code = ?', $sourceCode)->where('stock_id = ?',$stockId);
        $result = $conn->fetchOne($query);

        return (empty($result)) ? false : true;
    }

    public function getSourceStockIdWithSameSourceCode($sourceStockId) {
        if (empty($sourceStockId)) {
            return;
        }
        $conn = $this->getConnection();
        $selectSourceCodeQuery = $conn->select()->from($this->getMainTable(), 'source_code')->where('id = ?', $sourceStockId);
        $sourceCode = $conn->fetchOne($selectSourceCodeQuery);
        $selectAllSourceStockIdBySourceCode = $conn->select()->from($this->getMainTable(), 'id')->where('source_code = ?', $sourceCode);
        $rows = $conn->fetchAll($selectAllSourceStockIdBySourceCode);
        return $rows;
    }

    public function getVendorIdBySourceStockId($sourceStockId) {
        if (empty($sourceStockId)) {
            return;
        }
        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getMainTable(), 'vendor_id')->where('id = ?', $sourceStockId);
        return $conn->fetchOne($query);
    }

    public function getVendorIdBySourceCode($sourceCode) {
        if (empty($sourceCode)) {
            return;
        }
        $conn = $this->getConnection();
        $query = $conn->select()->from($this->getTable('inventory_source'), 'vendor_id')->where('source_code = ?', $sourceCode);
        return $conn->fetchOne($query);
    }
}
