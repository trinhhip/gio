<?php

namespace Omnyfy\Vendor\Controller\Adminhtml\Source;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\InventoryCatalogAdminUi\Model\BulkOperationsConfig;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;
use Psr\Log\LoggerInterface;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;
use Omnyfy\Vendor\Model\Resource\VendorSourceStock;
use Omnyfy\Vendor\Model\Resource\Inventory;
use Omnyfy\Vendor\Helper\Data as DataHelper;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class BulkTransferPost extends \Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Inventory\BulkTransferPost
{
    private $bulkSessionProductsStorage;
    private $bulkInventoryTransfer;
    private $bulkOperationsConfig;
    private $authSession;
    private $massSchedule;
    private $logger;
    private $vSourceStockResource;
    private $inventoryResource;
    private $sourceCollectionFactory;
    private $dataHelper;
    private $productTypeConfigurable;
    private $productCollectionFactory;

    public function __construct(
        Action\Context $context,
        BulkInventoryTransferInterface $bulkInventoryTransfer,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        BulkOperationsConfig $bulkOperationsConfig,
        LoggerInterface $logger,
        MassSchedule $massSchedule,
        VendorSourceStock $vSourceStockResource,
        Inventory $inventoryResource,
        SourceCollectionFactory $sourceCollectionFactory,
        DataHelper $dataHelper,
        Configurable $productTypeConfigurable,
        ProductCollectionFactory $productCollectionFactory
    ) {
        parent::__construct($context, $bulkInventoryTransfer, $bulkSessionProductsStorage, $bulkOperationsConfig, $logger, $massSchedule);

        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->bulkInventoryTransfer = $bulkInventoryTransfer;
        $this->authSession = $context->getAuth();
        $this->bulkOperationsConfig = $bulkOperationsConfig;
        $this->massSchedule = $massSchedule;
        $this->logger = $logger;
        $this->vSourceStockResource = $vSourceStockResource;
        $this->inventoryResource = $inventoryResource;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->dataHelper = $dataHelper;
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    private function runSynchronousOperation(
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignSource
    ): void {
        $count = $this->bulkInventoryTransfer->execute($skus, $originSource, $destinationSource, $unassignSource);
        $this->messageManager->addSuccessMessage(__('Bulk operation was successful: %count inventory transfers.', [
            'count' => $count
        ]));
    }

    private function runAsynchronousOperation(
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignSource
    ): void {
        $batchSize = $this->bulkOperationsConfig->getBatchSize();
        $userId = (int) $this->authSession->getUser()->getId();

        $skusChunks = array_chunk($skus, $batchSize);
        $operations = [];
        foreach ($skusChunks as $skuChunk) {
            $operations[] = [
                'skus' => $skuChunk,
                'originSource' => $originSource,
                'destinationSource' => $destinationSource,
                'unassignFromOrigin' => $unassignSource,
            ];
        }

        $this->massSchedule->publishMass(
            'async.V1.inventory.bulk-product-source-transfer.POST',
            $operations,
            null,
            $userId
        );

        $this->messageManager->addSuccessMessage(__('Your request was successfully queued for asynchronous execution'));
    }

    public function execute()
    {
        $originSource = $this->getRequest()->getParam('origin_source', '');
        $destinationSource = $this->getRequest()->getParam('destination_source', '');

        $skus = $this->bulkSessionProductsStorage->getProductsSkus();
        $unassignSource = (bool) $this->getRequest()->getParam('unassign_origin_source', false);

        $async = $this->bulkOperationsConfig->isAsyncEnabled();

        try {
            $sourceCollection = $this->sourceCollectionFactory->create();
            $vendorIdOfOriginSource = $sourceCollection->getItemById($originSource)->getVendorId();
            $vendorIdOfDestinationSource = $sourceCollection->getItemById($destinationSource)->getVendorId();
            $countSuccess = 0;
            $countError = 0;
            if ($vendorIdOfOriginSource == $vendorIdOfDestinationSource) {
                if ($async) {
                    $this->runAsynchronousOperation($skus, $originSource, $destinationSource, $unassignSource);
                } else {
                    $this->runSynchronousOperation($skus, $originSource, $destinationSource, $unassignSource);
                }
                $productCollection = $this->productCollectionFactory->create();
                foreach ($skus as $sku) {
                    $productId = $this->dataHelper->getProductIdBySku($sku);
                    $parentIds = $this->productTypeConfigurable->getParentIdsByChild($productId);
                    $sourceStockId = $this->dataHelper->getSourceStockIdBySourceCode($originSource);
                    $originSourceQty = 0;
                    $qtySaveToDestinationSource = 0;
                    $destinationSourceQty = $this->inventoryResource->getQty($destinationSource, $sku);
                    if ($unassignSource) {
                        $originSourceQty = $this->inventoryResource->getQty($originSource, $sku);
                        $this->inventoryResource->removeBySourceCode($originSource, $sku);
                        foreach ($parentIds as $parentId) {
                            if ($this->inventoryResource->isNoChildProduct($parentId, $originSource)) {
                                $this->inventoryResource->removeByProducIdAndSourceCode($parentId, $originSource);
                            }
                        }
                    } else {
                        $originSourceQty = $this->inventoryResource->getQty($originSource, $sku);
                        $this->inventoryResource->updateQtyBySourceCode($originSource, $sku, $productId, $sourceStockId, 0);
                    }

                    $qtySaveToDestinationSource = $originSourceQty + $destinationSourceQty;

                    if ($this->inventoryResource->isProductAssigned($destinationSource, $sku)) {
                        $this->inventoryResource->updateQtyBySourceCode($destinationSource, $sku, $productId, $sourceStockId, $qtySaveToDestinationSource);
                    } else {
                        $sourceStockIds = $this->vSourceStockResource->getIdsBySourceCode($destinationSource);
                        foreach ($sourceStockIds as $id) {
                            if (!$this->inventoryResource->isProductAssignedToSourceStock($id, $sku)) {
                                $dataSaveInventry = [
                                    'product_id' => $productId,
                                    'sku' => $sku,
                                    'quantity' => $qtySaveToDestinationSource,
                                    'source_stock_id' => $id,
                                    'source_code' => $destinationSource
                                ];
                                $this->inventoryResource->saveNewData($dataSaveInventry);
                            }
                            foreach ($parentIds as $parentId) {
                                $parentSku = $productCollection->getItemById($parentId)->getSku();
                                if (!$this->inventoryResource->isProductAssignedToSourceStock($id, $parentSku)) {
                                    $dataSaveParent = [
                                        'product_id' => $parentId,
                                        'sku' => $parentSku,
                                        'quantity' => 0,
                                        'source_stock_id' => $id,
                                        'source_code' => $destinationSource
                                    ];
                                    $this->inventoryResource->saveNewData($dataSaveParent);
                                }
                            }
                        }
                    }
                }
                $countSuccess++;
            } else {
                $countError++;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        if ($countError > 0) {
            $this->messageManager->addErrorMessage($countError . ' when transfer source. Source and Product are not from the same Vendor.');
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('catalog/product/index');
    }

    private function getSourceItemData(string $sku, string $source): ?array
    {
        $connection = $this->inventoryResource->getConnection();
        $tableName = $this->connection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $query = $connection->select()->from($tableName)
            ->where(SourceItemInterface::SOURCE_CODE . ' = ?', $source)
            ->where(SourceItemInterface::SKU . ' = ?', $sku);

        $res = $connection->fetchRow($query);
        if (empty($res)) {
            return null;
        }

        return $res;
    }
}
