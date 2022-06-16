<?php
namespace Omnyfy\Vendor\Controller\Adminhtml\Source;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\InventoryCatalogAdminUi\Model\BulkOperationsConfig;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryCatalogApi\Api\BulkSourceUnassignInterface;
use Psr\Log\LoggerInterface;
use Omnyfy\Vendor\Model\Resource\VendorSourceStock;
use Omnyfy\Vendor\Model\Resource\Inventory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class BulkUnassignPost extends \Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Source\BulkUnassignPost
{
    private $bulkSessionProductsStorage;
    private $bulkSourceUnassign;
    private $bulkOperationsConfig;
    private $massSchedule;
    private $authSession;
    private $logger;
    private $vSourceStockResource;
    private $inventoryResource;
    private $productCollectionFactory;
    private $productTypeConfigurable;

    public function __construct(
        Action\Context $context,
        BulkSourceUnassignInterface $bulkSourceUnassign,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        BulkOperationsConfig $bulkOperationsConfig,
        MassSchedule $massSchedule,
        LoggerInterface $logger,
        VendorSourceStock $vSourceStockResource,
        Inventory $inventoryResource,
        ProductCollectionFactory $productCollectionFactory,
        Configurable $productTypeConfigurable
    ) {
        parent::__construct($context, $bulkSourceUnassign, $bulkSessionProductsStorage, $bulkOperationsConfig, $massSchedule, $logger);

        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->bulkSourceUnassign = $bulkSourceUnassign;
        $this->bulkOperationsConfig = $bulkOperationsConfig;
        $this->massSchedule = $massSchedule;
        $this->authSession = $context->getAuth();
        $this->logger = $logger;
        $this->vSourceStockResource = $vSourceStockResource;
        $this->inventoryResource = $inventoryResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productTypeConfigurable = $productTypeConfigurable;
    }

    private function runSynchronousOperation(array $skus, array $sourceCodes): void
    {
        $count = $this->bulkSourceUnassign->execute($skus, $sourceCodes);
        $this->messageManager->addSuccessMessage(__('Bulk operation was successful: %count unassignments.', [
            'count' => $count
        ]));
    }

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
            'async.V1.inventory.bulk-product-source-unassign.POST',
            $operations,
            null,
            $userId
        );

        $this->messageManager->addSuccessMessage(__('Your request was successfully queued for asynchronous execution'));
    }

    public function execute()
    {
        $sourceCodes = $this->getRequest()->getParam('sources', []);
        $skus = $this->bulkSessionProductsStorage->getProductsSkus();

        $async = $this->bulkOperationsConfig->isAsyncEnabled();

        try {
            if ($async) {
                $this->runAsynchronousOperation($skus, $sourceCodes);
            } else {
                $this->runSynchronousOperation($skus, $sourceCodes);
            }
            $productCollection = $this->productCollectionFactory->create();
            foreach ($skus as $sku) {  
                $productId = $productCollection->getItemByColumnValue('sku', $sku)->getId();   
                $parentIds = $this->productTypeConfigurable->getParentIdsByChild($productId);
                $existSourceCodes = $this->inventoryResource->getSourceCodeBySku($sku);   
                foreach ($sourceCodes as $code) {   
                    if (in_array($code, $existSourceCodes)) {
                        $this->inventoryResource->removeBySourceCode($code, $sku);
                        foreach ($parentIds as $parentId) {
                            if ($this->inventoryResource->isNoChildProduct($parentId, $code)) {
                                $this->inventoryResource->removeByProducIdAndSourceCode($parentId, $code);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something went wrong during the operation.'));
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('catalog/product/index');
    }
}
