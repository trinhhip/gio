<?php

namespace Omnyfy\Vendor\Plugin\InventorySalesAdminUi;

use Magento\InventorySalesAdminUi\Model\ResourceModel\GetAssignedStockIdsBySku;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

class GetSalableQuantityDataBySkuPlugin
{
    private $getProductSalableQty;
    private $stockRepository;
    private $getAssignedStockIdsBySku;
    private $getStockItemConfiguration;
    protected $productCollectionFactory;
    protected $resourceConnection;

    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        StockRepositoryInterface $stockRepository,
        GetAssignedStockIdsBySku $getAssignedStockIdsBySku,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        CollectionFactory $productCollectionFactory,
        ResourceConnection $resourceConnection

    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->stockRepository = $stockRepository;
        $this->getAssignedStockIdsBySku = $getAssignedStockIdsBySku;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function aroundExecute($subject, callable $proceed, $sku)
    {
        $proceed($sku);
        $stockInfo = [];
        $stockIds = $this->getAssignedStockIdsBySku->execute($sku);
        $connection = $this->resourceConnection->getConnection();
        $productCollection = $this->productCollectionFactory->create();
        $productId = $productCollection->getItemByColumnValue('sku', $sku)->getId();
        if (count($stockIds)) {
            foreach ($stockIds as $stockId) {
                $sql = $connection->select()->from('omnyfy_vendor_vendor_product')->where("product_id = $productId");
                $row = $connection->fetchAll($sql);
                $vendorId = null;
                if (!empty($row)) {
                    $vendorId = $row[0]['vendor_id'];
                }
                $stockId = (int)$stockId;
                $stock = $this->stockRepository->get($stockId);
                $vendorIdd =  $stock->getVendorId();
                if ($vendorId == $stock->getVendorId()) {
                    $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
                    $isManageStock = $stockItemConfiguration->isManageStock();
                    $stockInfo[] = [
                        'stock_name' => $stock->getName(),
                        'qty' => $isManageStock ? $this->getProductSalableQty->execute($sku, $stockId) : null,
                        'manage_stock' => $isManageStock,
                    ];
                } else {
                    $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
                    $isManageStock = $stockItemConfiguration->isManageStock();
                    $stockInfo[] = [
                        'stock_name' => $stock->getName(),
                        'qty' => 0,
                        'manage_stock' => $isManageStock,
                    ];
                }
            }
        }
        return $stockInfo;
    }
}
