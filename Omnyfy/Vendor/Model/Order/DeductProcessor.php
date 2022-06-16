<?php

namespace Omnyfy\Vendor\Model\Order;

/**
 * Deduct Product's quantity when submit invoce, shipment
 */
class DeductProcessor
{
    protected $resourceConnection;
    protected $vendorResource;
    protected $sourceItemCollectionFactory;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory $sourceItemCollectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->vendorResource = $vendorResource;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
    }

    /**
     * Execute deduct product's qty 
     */
    public function execute(array $data, $isInvoice = null)
    {
        if (empty($data)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $sourceItemCollection = $this->sourceItemCollectionFactory->create();
        foreach ($data as $item) {
            if (!$this->isDeducted($item)) {
                $sourceItem = $sourceItemCollection->addFieldToFilter('source_code', $item['source_code'])
                    ->addFieldToFilter('sku', $item['sku'])
                    ->getFirstItem();
                $oldQty = $sourceItem->getQuantity();
                // Only deduct product's qty when product's qty greater or equal item shipment qty
                if (($oldQty - $item['quantity']) >= 0) {
                    $dataRevert = [
                        'order_id' => $item['order_id'],
                        'stock_id' => $item['stock_id'],
                        'sku' => $item['sku']
                    ];
                    $dataDeduct = [
                        'sku' => $item['sku'],
                        'qty' => $item['quantity'],
                        'source_code' => $item['source_code']
                    ];

                    // Add data to table omnyfy_inventory_reservation to mark that this product's qty is deducted so it will not be deducted again
                    $conn->insertOnDuplicate('omnyfy_inventory_reservation', $dataRevert);
                    $this->vendorResource->deductQty($dataDeduct);
                    
                    // If function was called when submiting invoice, the QTY in table inventory_source_item will be deducted too
                    if ($isInvoice) {
                        $newQty = $oldQty - $item['quantity'];
                        $sourceItem->setQuantity($newQty);
                        $sourceItem->save();
                        $sourceItemCollection->load();
                    }
                }
            }
        }
    }

    public function isDeducted($item)
    {
        $conn = $this->resourceConnection->getConnection();
        $query = $conn->select()->from('omnyfy_inventory_reservation', 'id')
            ->where('order_id = ?', $item['order_id'])
            ->where('sku = ?', $item['sku'])
            ->where('stock_id = ?', $item['stock_id']);
        $result = $conn->fetchOne($query);

        return empty($result) ? false : true;
    }

    public function deductStockQty($data) {
        $conn = $this->resourceConnection->getConnection();
        $stockTable = 'inventory_stock_' . $data['stock_id'];
        $deductQty = $data['quantity'];
        $sku = $data['sku'];
        $updateQuery = "UPDATE $stockTable SET quantity = quantity - $deductQty WHERE sku = '$sku'";
        $conn->query($updateQuery);
    }
}
