<?php

namespace Omnyfy\Vendor\Plugin\Import;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class DeleteMultiple
{

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function afterExecute(\Magento\Inventory\Model\ResourceModel\SourceItem\DeleteMultiple $subject, $result, array $sourceItems)
    {
        if (!count($sourceItems)) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('omnyfy_vendor_inventory');

        $whereSql = $this->buildWhereSqlPart($sourceItems);
        $connection->delete($tableName, $whereSql);
        return $result;
    }

    /**
     * @param array $sourceItems
     * @return string
     */
    private function buildWhereSqlPart(array $sourceItems): string
    {
        $connection = $this->resourceConnection->getConnection();

        $condition = [];
        foreach ($sourceItems as $sourceItem) {
            $skuCondition = $connection->quoteInto(
                'sku' . ' = ?',
                $sourceItem->getSku()
            );
            $sourceCodeCondition = $connection->quoteInto(
                'source_code' . ' = ?',
                $sourceItem->getSourceCode()
            );
            $condition[] = '(' . $skuCondition . ' AND ' . $sourceCodeCondition . ')';
        }
        return implode(' OR ', $condition);
    }
}
