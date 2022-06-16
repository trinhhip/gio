<?php

namespace Omnyfy\Vendor\Model;

use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class IsProductSaleableForSource
{
    private $getStockItemConfiguration;
    private $getStockItemData;
    private $getReservationsQuantity;
    private $isSourceItemManagementAllowedForProductType;
    private $getProductTypesBySkus;
    private $searchCriteriaBuilder;
    private $sourceItemRepository;
    private $resourceConnection;

    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfig,
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterface $getReservationsQuantity,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository
    ) {
        $this->getStockItemConfiguration = $getStockItemConfig;
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
    }

    public function execute($sku, $stockId, $sourceCode, $qty): float
    {
        $this->validateProductType($sku);
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        $stockItemConfig = $this->getStockItemConfiguration->execute($sku, $stockId);
        $minQty = $stockItemConfig->getMinQty();

        if (null === $stockItemData || (bool)$stockItemData[GetStockItemDataInterface::IS_SALABLE] === false) {
            return false;
        }

        $stockQtyBySource = $this->getStockItemQtyBySource($sku, $sourceCode);
        if ($stockQtyBySource && $stockQtyBySource->getStatus()) {
            $productQtyInStock = $stockQtyBySource->getQuantity() - $minQty - $qty;
        } else {
            return false;
        }

        return $productQtyInStock >= 0 ? true : false;
    }

    private function validateProductType(string $sku): void
    {
        $productTypesBySkus = $this->getProductTypesBySkus->execute([$sku]);
        if (!array_key_exists($sku, $productTypesBySkus)) {
            throw new NoSuchEntityException(
                __('The product that was requested doesn\'t exist. Verify the product and try again.')
            );
        }

        $productType = $productTypesBySkus[$sku];

        if (false === $this->isSourceItemManagementAllowedForProductType->execute($productType)) {
            throw new InputException(
                __('Can\'t check requested quantity for products without Source Items support.')
            );
        }
    }

    private function getStockItemQtyBySource($sku, $sourceCode)
    {
        if (empty($sku) || empty($sourceCode)) {
            return;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        foreach ($sourceItems as $sourceItem) {
            return $sourceItem;
        }
        return false;
    }
}
