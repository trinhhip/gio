<?php

namespace Omnyfy\Vendor\Controller\Adminhtml\Source;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\BulkException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalogAdminUi\Model\BulkOperationsConfig;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryCatalogApi\Api\BulkSourceAssignInterface;
use Psr\Log\LoggerInterface;
use Omnyfy\Vendor\Model\Resource\VendorSourceStock;
use Omnyfy\Vendor\Model\Resource\Inventory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;

class BulkAssignPost extends \Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Source\BulkAssignPost
{
    private $bulkSessionProductsStorage;
    private $bulkSourceAssign;
    private $massSchedule;
    private $authSession;
    private $bulkOperationsConfig;
    private $logger;
    private $vSourceStockResource;
    private $inventoryResource;
    private $productCollectionFactory;
    private $productTypeConfigurable;
    private $resourceConnection;
    private $sourceCollectionFactory;

    public function __construct(
        Action\Context $context,
        BulkSourceAssignInterface $bulkSourceAssign,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        BulkOperationsConfig $bulkOperationsConfig,
        MassSchedule $massSchedule,
        LoggerInterface $logger,
        VendorSourceStock $vSourceStockResource,
        Inventory $inventoryResource,
        ProductCollectionFactory $productCollectionFactory,
        Configurable $productTypeConfigurable,
        ResourceConnection $resourceConnection,
        SourceCollectionFactory $sourceCollectionFactory
    ) {
        parent::__construct($context, $bulkSourceAssign, $bulkSessionProductsStorage, $bulkOperationsConfig, $massSchedule, $logger);
        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->bulkSourceAssign = $bulkSourceAssign;
        $this->massSchedule = $massSchedule;
        $this->authSession = $context->getAuth();
        $this->bulkOperationsConfig = $bulkOperationsConfig;
        $this->logger = $logger;
        $this->vSourceStockResource = $vSourceStockResource;
        $this->inventoryResource = $inventoryResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->resourceConnection = $resourceConnection;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
    }

    /**
     * @param array $skus
     * @param array $sourceCodes
     * @return void
     * @throws ValidationException
     */
    private function runSynchronousOperation(array $skus, array $sourceCodes): void
    {
        $count = $this->bulkSourceAssign->execute($skus, $sourceCodes);
    }

    /**
     * @param array $skus
     * @param array $sourceCodes
     * @return void
     * @throws BulkException
     * @throws LocalizedException
     */
    private function runAsynchronousOperation(array $skus, array $sourceCodes): void
    {
        $batchSize = $this->bulkOperationsConfig->getBatchSize();
        $userId = (int) $this->authSession->getUser()->getId();

        $skusChunks = array_chunk($skus, $batchSize);
        $operations = [];
        foreach ($skusChunks as $skuChunk) {
            $operations[] = [
                'skus' => $skuChunk,
                'sourceCodes' => $sourceCodes,
            ];
        }

        $this->massSchedule->publishMass(
            'async.V1.inventory.bulk-product-source-assign.POST',
            $operations,
            null,
            $userId
        );

        $this->messageManager->addSuccessMessage(__('Your request was successfully queued for asynchronous execution'));
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $sourceCodes = $this->getRequest()->getParam('sources', []);
        $skus = $this->bulkSessionProductsStorage->getProductsSkus();
        $async = $this->bulkOperationsConfig->isAsyncEnabled();
        $sourceCollection = $this->sourceCollectionFactory->create();
        $productCollection = $this->productCollectionFactory->create();
        $err = 0;
        $success = 0;
        try {
            foreach ($skus as $sku) { 
                $vendorIdOfProduct = $this->getVendorIdOfProduct($sku);
                $productId = $productCollection->getItemByColumnValue('sku', $sku)->getId();
                $parentIds = $this->productTypeConfigurable->getParentIdsByChild($productId);
                $isChildProduct = false;
                if (!empty($parentIds)) {
                    $isChildProduct = true;
                }
                foreach ($sourceCodes as $code) {
                    $vendorIdOfSource = $sourceCollection->getItemById($code)->getVendorId();
                    $saveSourceCode[] = $code;
                    if (($vendorIdOfProduct == $vendorIdOfSource) && ($vendorIdOfProduct != null) && ($vendorIdOfSource != null)) {
                        if ($async) {
                            $this->runAsynchronousOperation($skus, $saveSourceCode);
                            $success++;
                        } else {
                            $this->runSynchronousOperation($skus, $saveSourceCode);
                            $success++;
                        }
                        $sourceStockIds = $this->vSourceStockResource->getIdsBySourceCode($code);
                        $oldQty = [];
                        $oldQty[$code] = 0;
                        foreach ($sourceStockIds as $id) {
                            $qty = $this->inventoryResource->getOldQty($id, $sku);
                            if ($qty != 0) {
                                $oldQty[$code] = $qty;
                            }
                        }
                        /** If current product is child product 
                         *  If parent product is not assinged to current source in table omnyfy_vendor_inventory, will add it.
                        */
                        foreach ($sourceStockIds as $id) {
                            if ($isChildProduct) {
                                foreach ($parentIds as $parentId) {
                                    if (!$this->inventoryResource->isParentProductExists($parentId, $code)) {
                                        $parentSku = $productCollection->getItemById($parentId)->getSku();
                                        $dataSaveParent = [
                                            'product_id' => $parentId,
                                            'source_code' => $code,
                                            'sku' => $parentSku,
                                            'quantity' => 0,
                                            'source_stock_id' => $id
                                        ];
                                        $this->inventoryResource->saveDuplicateData($dataSaveParent);
                                    }
                                }
                            }
                            if ($this->inventoryResource->isAssigned($id, $sku)) {
                                continue;
                            }
                            $dataSaveChildren = [
                                'product_id' => $productId,
                                'source_stock_id' => $id,
                                'sku' => $sku,
                                'quantity' => ($oldQty[$code] > 0) ? $oldQty : 0,
                                'source_code' => $code
                            ];
                            $this->inventoryResource->saveDuplicateData($dataSaveChildren);
                        }
                    } else {
                        $err++;
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something went wrong during the operation.'));
        }

        if ($success > 0) {
            $this->messageManager->addSuccessMessage(__('Bulk operation was successful: %count assignments.', [
                'count' => $success
            ]));
        }

        if ($err > 0) {
            $this->messageManager->addErrorMessage(__($err . ' products assigned false. Product and Source are not from the same Vendor'));
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('catalog/product/index');
    }

    public function getVendorIdOfProduct($sku) {
        $conn = $this->resourceConnection->getConnection();
        $selectProductId = $conn->select()->from('catalog_product_entity', 'entity_id')->where('sku = ?', $sku);
        $productId = $conn->fetchOne($selectProductId);
        $selectVendorId = $conn->select()->from('omnyfy_vendor_vendor_product', 'vendor_id')->where('product_id = ?', $productId);
        
        return $conn->fetchOne($selectVendorId);
    }
}
