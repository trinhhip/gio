<?php

namespace Omnyfy\Vendor\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;

class AssignSourceToStockObserver implements ObserverInterface
{
    const SOURCE_STOCK_LINK_TABLE = 'inventory_source_stock_link';
    const OMNYFY_VENDOR_SOURCE_STOCK_TABLE = 'omnyfy_vendor_source_stock';
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        $postValue = $observer->getRequest()->getPostValue();
        $sourceData = $postValue['general'];
        $stockIds = $sourceData['stock'];
        $stockIdsBySourceCode = $this->getAllLinkOfSource($sourceData['source_code']);

        if (!empty($stockIds)) {
            if (!empty($stockIdsBySourceCode)) {
                foreach ($stockIdsBySourceCode as $stockId) {
                    if (!in_array($stockId, $stockIds)) {
                        $this->removeLink($sourceData['source_code'], $stockId, self::SOURCE_STOCK_LINK_TABLE);
                        $this->removeLink($sourceData['source_code'], $stockId, self::OMNYFY_VENDOR_SOURCE_STOCK_TABLE);
                    } else {
                        $this->update($sourceData['source_code'], $stockId, $sourceData['vendor_id']);
                    }
                }
            }

            foreach ($stockIds as $stockId) {
                $stockId = (int)$stockId;
                if (!$this->isLinkCreated($sourceData['source_code'], $stockId)) {
                    $this->saveToSourceStockLink($sourceData['source_code'], $stockId);
                    $this->saveToOmnyfyVendorSourceStock($sourceData['source_code'], $stockId, $sourceData['vendor_id']);
                }
            }
        } else {
            // remove all link
            $conn = $this->resourceConnection->getConnection();
            $conn->delete(self::SOURCE_STOCK_LINK_TABLE, ['source_code = ?' => $sourceData['source_code']]);
            $conn->delete(self::OMNYFY_VENDOR_SOURCE_STOCK_TABLE, ['source_code = ?' => $sourceData['source_code']]);
        }

    }

    public function saveToSourceStockLink($sourceCode, $stockId) {
        if (empty($sourceCode) || empty($stockId)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();

        $priority = (int)$this->createPriority($sourceCode, $stockId);
        $insertQuery = 'INSERT INTO inventory_source_stock_link(source_code, stock_id, priority) VALUES( "'.$sourceCode.'", '.$stockId.','.$priority .')';
        $conn->query($insertQuery);
    }

    public function saveToOmnyfyVendorSourceStock($sourceCode, $stockId, $vendorId) {
        if (empty($sourceCode) || empty($stockId) || empty($vendorId)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $insertQuery = 'INSERT INTO omnyfy_vendor_source_stock(source_code, stock_id, vendor_id) VALUES("'.$sourceCode.'",'. $stockId.','. $vendorId.')';
        $conn->query($insertQuery);
    }

    public function createPriority($sourceCode, $stockId) {
        if (empty($sourceCode) || empty($stockId)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $query = $conn->select()->from(self::SOURCE_STOCK_LINK_TABLE, ['priority'])
                        ->where("stock_id = $stockId")
                        ->order('priority DESC');
        $result = $conn->fetchAll($query);

        if (empty($result)) {
            // if Stock has no Source set 2 for first Source's priority
            return 2;
        } else {
            return $result[0]['priority'] + 2;
        }

        return $result;
    }

    public function isLinkCreated($sourceCode, $stockId) {
        if (empty($sourceCode) || empty($stockId)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $query = $conn->select()->from(self::SOURCE_STOCK_LINK_TABLE, 'link_id')
                                ->where('stock_id = ?', $stockId)
                                ->where('source_code = ?', $sourceCode);
        $result = $conn->fetchAll($query);

        return (count($result) > 0) ? true : false;
    }

    public function getAllLinkOfSource($sourceCode) {
        if (empty($sourceCode)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $query = $conn->select()->from(self::SOURCE_STOCK_LINK_TABLE, 'stock_id')->where('source_code = ?', $sourceCode);
        $rows = $conn->fetchCol($query);
        // $result = [];
        // foreach ($rows as $row) {
        //     $result[] = $row['stock_id'];
        // }

        return $rows;
    }

    public function removeLink($sourceCode, $stockId, $table) {
        if (empty($sourceCode) || empty($stockId)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $conn->delete($table, ['source_code = ?' => $sourceCode, 'stock_id = ?' => $stockId]);

    }

    public function update($sourceCode, $stockId, $vendorId) {
        if (empty($sourceCode) || empty($stockId) || empty($vendorId)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $conn->update(self::OMNYFY_VENDOR_SOURCE_STOCK_TABLE, ['vendor_id' => $vendorId], ['source_code = ?' => $sourceCode, 'stock_id = ?' => $stockId]);
    }
}
