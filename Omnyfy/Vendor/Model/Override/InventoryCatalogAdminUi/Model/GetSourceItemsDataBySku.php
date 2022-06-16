<?php
namespace Omnyfy\Vendor\Model\Override\InventoryCatalogAdminUi\Model;

use Omnyfy\Vendor\Model\Resource\VendorSourceStock\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryIndexer\Model\ResourceModel\GetStockItemData;

class GetSourceItemsDataBySku extends \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku
{
    private $sourceItemRepository;
    private $sourceRepository;
    private $searchCriteriaBuilder;
    protected $vSourceStockFactory;
    protected $getStockItemData;

    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        GetStockItemData $getStockItemData
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->vSourceStockFactory = $collectionFactory;
        $this->getStockItemData = $getStockItemData;
    }

    public function execute(string $sku): array
    {
        $sourceItemsData = [];
        $collection = $this->vSourceStockFactory->create();
        $collection->joinInventory();
        $sourceItems = $collection->addFieldToFilter('sku', $sku)->getItems();

        $sourcesCache = [];
        foreach ($sourceItems as $sourceItem) {
            $sourceCode = $sourceItem->getSourceCode();
            $stockId = $sourceItem->getStockId();
            $stockItemData = $this->getStockItemData->execute($sku, $stockId);
            $stockQty = 0;
            if (!empty($stockItemData)) {
                $stockQty = $stockItemData['quantity'];
            }

            if (!isset($sourcesCache[$sourceCode])) {
                $sourcesCache[$sourceCode] = $this->sourceRepository->get($sourceCode);
            }

            $source = $sourcesCache[$sourceCode];

            $sourceItemsData[] = [
                'id' => $sourceItem->getId(),
                SourceItemInterface::SOURCE_CODE => $sourceItem->getSourceCode(),
                SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                SourceItemInterface::STATUS => $sourceItem->getStatus(),
                SourceInterface::NAME => $source->getName(),
                'source_status' => $source->isEnabled(),
                'stock_qty' => $stockQty
            ];
        }

        return $sourceItemsData;
    }
}
