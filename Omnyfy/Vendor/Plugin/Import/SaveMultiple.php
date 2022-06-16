<?php

namespace Omnyfy\Vendor\Plugin\Import;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class SaveMultiple
{

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    protected $vendorHelper;

    protected $_productRepository;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Omnyfy\Vendor\Helper\Data $vendorHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->_productRepository = $productRepository;
        $this->vendorHelper = $vendorHelper;
    }

    public function afterExecute(\Magento\Inventory\Model\ResourceModel\SourceItem\SaveMultiple $subject, $result, array $sourceItems)
    {
        if (!count($sourceItems)) {
            return;
        }
        foreach ($sourceItems as $index => $item) {
            $sku = $item->getSku();
            $productType = $this->_productRepository->get($sku)->getTypeId();
            if ($productType == 'configurable' || $productType == 'bundle') {
                unset($sourceItems[$index]);
            }
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('omnyfy_vendor_inventory');

        $columnsSql = $this->buildColumnsSqlPart([
            'product_id',
            'source_stock_id',
            'quantity',
            'sku',
            'source_code'
        ]);
        $valuesSql = $this->buildValuesSqlPart($sourceItems);
        $onDuplicateSql = $this->buildOnDuplicateSqlPart([
            SourceItemInterface::QUANTITY
        ]);
        $bind = $this->getSqlBindData($sourceItems);

        if ($bind) {
            $insertSql = sprintf(
                'INSERT INTO `%s` (%s) VALUES %s %s',
                $tableName,
                $columnsSql,
                $valuesSql,
                $onDuplicateSql
            );
            $connection->query($insertSql, $bind);
        }

        return $result;
    }

    /**
     * Build column sql part
     *
     * @param array $columns
     * @return string
     */
    private function buildColumnsSqlPart(array $columns): string
    {
        $connection = $this->resourceConnection->getConnection();
        $processedColumns = array_map([$connection, 'quoteIdentifier'], $columns);
        $sql = implode(', ', $processedColumns);
        return $sql;
    }

    /**
     * Build sql query for values
     *
     * @param SourceItemInterface[] $sourceItems
     * @return string
     */
    private function buildValuesSqlPart(array $sourceItems): string
    {
        foreach ($sourceItems as $index => $item) {
            if ($item->getSourceCode() == 'default') {
                unset($sourceItems[$index]);
            }
        }
        $sql = rtrim(str_repeat('(?, ?, ?, ?, ?), ', count($sourceItems)), ', ');
        return $sql;
    }

    /**
     * Get Sql bind data
     *
     * @param SourceItemInterface[] $sourceItems
     * @return array
     */
    private function getSqlBindData(array $sourceItems): array
    {
        $bind = [];
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getSourceCode() != 'default') {
                $sourceStockId = $this->vendorHelper->getSourceStockIdBySourceCode($sourceItem->getSourceCode());
                $productId = $this->getProductBySku($sourceItem->getSku());
                $bind[] = $productId;
                $bind[] = $sourceStockId;
                $bind[] = $sourceItem->getQuantity();
                $bind[] = $sourceItem->getSku();
                $bind[] = $sourceItem->getSourceCode();
            }
        }
        return $bind;
    }

    /**
     * Build sql query for on duplicate event
     *
     * @param array $fields
     * @return string
     */
    private function buildOnDuplicateSqlPart(array $fields): string
    {
        $connection = $this->resourceConnection->getConnection();
        $processedFields = [];
        foreach ($fields as $field) {
            $processedFields[] = sprintf('%1$s = VALUES(%1$s)', $connection->quoteIdentifier($field));
        }
        $sql = 'ON DUPLICATE KEY UPDATE ' . implode(', ', $processedFields);
        return $sql;
    }

    private function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku)->getId();
    }
}
