<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Plugin\CatalogSearch\Model\Search\FilterMapper;

use Amasty\ShopbyBase\Model\Di\Wrapper;
use Magento\CatalogSearch\Model\Search\FilterMapper\StockStatusFilter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;

class StockStatusFilterPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        Wrapper $stockResolver,
        Wrapper $defaultStockProvider,
        Wrapper $stockIndexTableNameResolver
    ) {
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Plugin fixes stock filter for search request select and cuts products, which aren't assigned to any source
     *
     * @param StockStatusFilter $subject
     * @param callable $proceed
     * @param Select $select
     * @param array $stockValues
     * @param string $type
     * @param bool $showOutOfStockFlag
     * @return Select
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundApply(
        StockStatusFilter $subject,
        callable $proceed,
        Select $select,
        $stockValues,
        $type,
        $showOutOfStockFlag
    ): Select {
        try {
            $stockId = $this->getStockId();
            if ($stockId === null || $stockId === $this->defaultStockProvider->getId()) {
                return $proceed($select, $stockValues, $type, $showOutOfStockFlag);
            }

            if ($type !== StockStatusFilter::FILTER_JUST_ENTITY
                && $type !== StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS
            ) {
                throw new \InvalidArgumentException('Invalid filter type: ' . $type);
            }

            $mainTableAlias = $this->extractTableAliasFromSelect($select);
            $select->joinInner(
                ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                sprintf('product.entity_id = %s.entity_id', $mainTableAlias),
                []
            );
            $this->addInventoryStockJoin($select, $showOutOfStockFlag);

            if ($type === StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS) {
                $select->joinInner(
                    ['sub_product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                    sprintf('sub_product.entity_id = %s.source_id', $mainTableAlias),
                    []
                );
                $this->addSubProductInventoryStockJoin($select, $showOutOfStockFlag);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        return $select;
    }

    /**
     * @param Select $select
     * @param bool $showOutOfStockFlag
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addInventoryStockJoin(Select $select, bool $showOutOfStockFlag): void
    {
        $stockTableName = $this->getStockTableName();
        if ($stockTableName) {
            $select->joinInner(
                ['stock_index' => $stockTableName],
                'stock_index.sku = product.sku',
                []
            );
            if ($showOutOfStockFlag === false) {
                $select->where(sprintf('stock_index.%s = %s', IndexStructure::IS_SALABLE, 1));
            }
        }
    }

    /**
     * @param Select $select
     * @param bool $showOutOfStockFlag
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addSubProductInventoryStockJoin(Select $select, bool $showOutOfStockFlag): void
    {
        $stockTableName = $this->getStockTableName();
        if ($stockTableName) {
            $select->joinInner(
                ['sub_product_stock_index' => $stockTableName],
                'sub_product_stock_index.sku = sub_product.sku',
                []
            );
            if ($showOutOfStockFlag === false) {
                $select->where(sprintf('sub_product_stock_index.%s = %s', IndexStructure::IS_SALABLE, 1));
            }
        }
    }

    /**
     * @param Select $select
     * @return string|null
     * @throws \Zend_Db_Select_Exception
     */
    private function extractTableAliasFromSelect(Select $select): ?string
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStockTableName(): ?string
    {
        $tableName = $this->stockIndexTableNameResolver->execute($this->getStockId());
        return $tableName ? $this->resourceConnection->getTableName($tableName) : null;
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStockId(): ?int
    {
        $stock = $this->stockResolver->execute(
            SalesChannelInterface::TYPE_WEBSITE,
            $this->storeManager->getWebsite()->getCode()
        );

        return $stock ? (int)$stock->getStockId() : null;
    }
}
