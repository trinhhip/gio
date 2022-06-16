<?php
/**
 * Project: Omnyfy Multi Vendor.
 * User: jing
 * Date: 1/5/17
 * Time: 1:43 PM
 */

namespace Omnyfy\Vendor\Plugin\Quote\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Exception\LocalizedException;

class StockItem
{
    protected $_stockRegistryProvider;

    protected $_stockStateProvider;

    protected $typeConfig;

    protected $quoteItemQtyList;

    protected $_extraHelper;

    protected $vSourceStockCollectionFactory;

    protected $stockState;

    protected $sourceRepository;

    protected $getDistanceFromSourceToAddress;

    protected $inventoryAddressFactory;

    protected $resourceConnection;

    protected $logger;

    protected $isProductSaleableForSource;

    protected $productRepository;

    protected $vSourceStock;

    public function __construct(
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistryProvider,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $typeConfig,
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList $quoteItemQtyList,
        \Omnyfy\Vendor\Helper\Extra $extraHelper,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock\CollectionFactory $vSourceStockCollectionFactory,
        \Magento\Inventory\Model\SourceRepository $sourceRepository,
        \Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GetDistanceFromSourceToAddress $getDistanceFromSourceToAddress,
        \Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory $inventoryAddressFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger,
        \Omnyfy\Vendor\Model\IsProductSaleableForSource $isProductSaleableForSource,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStock,
        StockStateInterface $stockState
    )
    {
        $this->_stockRegistryProvider = $stockRegistryProvider;
        $this->_stockStateProvider = $stockStateProvider;
        $this->typeConfig = $typeConfig;
        $this->quoteItemQtyList = $quoteItemQtyList;
        $this->_extraHelper = $extraHelper;
        $this->stockState = $stockState;
        $this->vSourceStockCollectionFactory = $vSourceStockCollectionFactory;
        $this->sourceRepository = $sourceRepository;
        $this->getDistanceFromSourceToAddress = $getDistanceFromSourceToAddress;
        $this->inventoryAddressFactory = $inventoryAddressFactory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->isProductSaleableForSource = $isProductSaleableForSource;
        $this->productRepository = $productRepository;
        $this->vSourceStock = $vSourceStock;
    }

    public function aroundInitialize(
        $subject,
        callable $proceed,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $qty
    )
    {
        $this->_extraHelper->parseExtraInfo(
            $quoteItem->getQuote()->getExtShippingInfo(),
            $stockItem
        );

        if ($stockItem->hasSessionLocationId() && !$stockItem->hasLocationId()) {
            $sessionLocationId = $stockItem->getSessionLocationId();
            if (!empty($sessionLocationId)) {
                $stockItem->setData('location_id', $sessionLocationId);
            }
        }

        if ($stockItem->hasSessionVendorId()) {
            $sessionVendorId = $stockItem->getSessionVendorId();
            if (!empty($sessionVendorId)) {
                if (!$stockItem->hasVendorId()) {
                    $stockItem->setData('vendor_id', $sessionVendorId);
                }
            }
        }

        $result = $this->_initialize($stockItem, $quoteItem, $qty);

        if (!empty($quoteItem->getBookingId())) {
            return $result;
        }

        $qtys = $stockItem->getQtys();
        $sourceStockIds = [];
        $distanceBySourceCode = [];
        $sourceCodeBySourceStockId = [];
        if (is_countable($qtys) && count($qtys) > 1) {
            $quoteAdress = $quoteItem->getAddress();
            $addressData = [
                'country' => $quoteAdress->getCountry(),
                'postcode' => $quoteAdress->getPostcode(),
                'street' => $quoteAdress->getStreet()[0],
                'region' => $quoteAdress->getRegion(),
                'city' => $quoteAdress->getCity()
            ];
            if (!empty($addressData['country']) && !empty($addressData['postcode']) && !empty($addressData['street']) && !empty($addressData['region']) && !empty($addressData['city'])) {
                $inventoryAddress = $this->inventoryAddressFactory->create($addressData);
                foreach ($qtys as $sourceStockId => $qty) {
                    $sourceStockIds[] = $sourceStockId;
                }
                $sourceStockCollection = $this->vSourceStockCollectionFactory->create()->addFieldToFilter('id', ['in' => $sourceStockIds]);
                foreach ($qtys as $sourceStockId => $quantity) {
                    $sourceCode = $sourceStockCollection->getItemById($sourceStockId)->getSourceCode();
                    $sourceCodeBySourceStockId[$sourceStockId] = $sourceCode;
                    $source = $this->sourceRepository->get($sourceCode);

                    try {
                        $distanceBySourceCode[$sourceStockId] = $this->getDistanceFromSourceToAddress->execute($source, $inventoryAddress);
                    } catch (LocalizedException $e) {
                        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/distance.log');
                        $logger = new \Zend\Log\Logger();
                        $logger->addWriter($writer);
                        $logger->info('Distance from shipping address to Source ' . $source->getName() . ': ' . $e->getMessage());
                    }
                }
                asort($distanceBySourceCode);
            }
        }
        $isCaculatedAllDistance = false;
        if (is_countable($qtys)) {
            if (!empty($distanceBySourceCode)) {
                $isCaculatedAllDistance = true;
            }
        }

        if ($stockItem->hasLocationId()) {
            $quoteItem->setData('location_id', $stockItem->getLocationId());
            $quoteItem->setData('source_stock_id', $stockItem->getLocationId());
            $this->_extraHelper->updateAddressItemLocationId($quoteItem->getId(), $stockItem->getLocationId());
        }
        elseif ($stockItem->hasQtys()){
            $sku = $this->productRepository->getById($stockItem->getProductId())->getSku();
            if (count($stockItem->getQtys()) > 1 && $isCaculatedAllDistance) {
                foreach ($distanceBySourceCode as $locationId => $distance) {
                    $stockId = $this->vSourceStock->getStockIdBySourceStockId($locationId);
                    if (!$stockItem->getManageStock()) {
                        $quoteItem->setData('location_id', $locationId);
                        $quoteItem->setData('source_stock_id', $locationId);
                        $this->_extraHelper->updateAddressItemLocationId($quoteItem->getId(), $stockItem->getLocationId());
                        $this->updateSourceStockId($quoteItem->getId(), $locationId);
                        break;
                    }
                    if ($this->isProductSaleableForSource->execute($sku, $stockId, $sourceCodeBySourceStockId[$locationId], $quoteItem->getQty())) {
                        $quoteItem->setData('location_id', $locationId);
                        $quoteItem->setData('source_stock_id', $locationId);
                        $this->_extraHelper->updateAddressItemLocationId($quoteItem->getId(), $stockItem->getLocationId());
                        $this->updateSourceStockId($quoteItem->getId(), $locationId);
                        break;
                    }
                }
            } else {
                foreach($stockItem->getQtys() as $locationId => $stockQty) {
                    if (empty($locationId)) {
                        continue;
                    }
                    $stockId = $this->vSourceStock->getStockIdBySourceStockId($locationId);
                    $sourceCode = $this->vSourceStock->getSourceCodeById($locationId);

                    if (!$stockItem->getManageStock()) {
                        $quoteItem->setData('location_id', $locationId);
                        $quoteItem->setData('source_stock_id', $locationId);
                        $this->_extraHelper->updateAddressItemLocationId($quoteItem->getId(), $stockItem->getLocationId());
                        break;
                    }
                    if ($this->isProductSaleableForSource->execute($sku, $stockId, $sourceCode, $quoteItem->getQty())) {
                        $quoteItem->setData('location_id', $locationId);
                        $quoteItem->setData('source_stock_id', $locationId);
                        $this->_extraHelper->updateAddressItemLocationId($quoteItem->getId(), $stockItem->getLocationId());
                        $this->updateSourceStockId($quoteItem->getId(), $locationId);
                        break;
                    }
                }
            }
        }
        if ($stockItem->hasVendorId()) {
            $quoteItem->setData('vendor_id', $stockItem->getVendorId());
        }

        return $result;
    }

    protected function _initialize(
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        $qty
    ) {

        $product = $quoteItem->getProduct();
        $quoteItemId = $quoteItem->getId();
        $quoteId = $quoteItem->getQuoteId();
        $productId = $product->getId();
        /**
         * When we work with subitem
         */
        if ($quoteItem->getParentItem()) {
            $rowQty = $quoteItem->getParentItem()->getQty() * $qty;
            /**
             * we are using 0 because original qty was processed
             */
            $qtyForCheck = $this->quoteItemQtyList
                ->getQty($productId, $quoteItemId, $quoteId, 0);
        } else {
            $increaseQty = $quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty;
            $rowQty = $qty;
            $qtyForCheck = $this->quoteItemQtyList->getQty(
                $productId,
                $quoteItemId,
                $quoteId,
                $increaseQty
            );
        }

        $productTypeCustomOption = $product->getCustomOption('product_type');
        if ($productTypeCustomOption !== null) {
            // Check if product related to current item is a part of product that represents product set
            if ($this->typeConfig->isProductSet($productTypeCustomOption->getValue())) {
                $stockItem->setIsChildItem(true);
            }
        }

        $stockItem->setProductName($product->getName());

        /** @var \Magento\Framework\DataObject $result */
        $result = $this->stockState->checkQuoteItemQty(
            $productId,
            $rowQty,
            $qtyForCheck,
            $qty,
            $product->getStore()->getWebsiteId()
        );

        if ($result->getHasError() === true && in_array($result->getErrorCode(), ['qty_available', 'out_stock'])) {
            $quoteItem->setHasError(true);
        }

        /* We need to ensure that any possible plugin will not erase the data */
        $backOrdersQty = $this->_stockStateProvider->checkQuoteItemQty($stockItem, $rowQty, $qtyForCheck, $qty)->getItemBackorders();
        $result->setItemBackorders($backOrdersQty);

        if ($stockItem->hasIsChildItem()) {
            $stockItem->unsIsChildItem();
        }

        if ($result->getItemIsQtyDecimal() !== null) {
            $quoteItem->setIsQtyDecimal($result->getItemIsQtyDecimal());
            if ($quoteItem->getParentItem()) {
                $quoteItem->getParentItem()->setIsQtyDecimal($result->getItemIsQtyDecimal());
            }
        }

        /**
         * Just base (parent) item qty can be changed
         * qty of child products are declared just during add process
         * exception for updating also managed by product type
         */
        if ($result->getHasQtyOptionUpdate() && (!$quoteItem->getParentItem() ||
                $quoteItem->getParentItem()->getProduct()->getTypeInstance()->getForceChildItemQtyChanges(
                    $quoteItem->getParentItem()->getProduct()
                )
            )
        ) {
            $quoteItem->setData('qty', $result->getOrigQty());
        }

        if ($result->getItemUseOldQty() !== null) {
            $quoteItem->setUseOldQty($result->getItemUseOldQty());
        }

        if ($result->getMessage() !== null) {
            $quoteItem->setMessage($result->getMessage());
        }

        if ($result->getItemBackorders() !== null) {
            $quoteItem->setBackorders($result->getItemBackorders());
        }

        $quoteItem->setStockStateResult($result);

        return $result;
    }

    public function updateSourceStockId($quoteItemId, $sourceStockId) {
        if (empty($quoteItemId) || empty($sourceStockId)) {
            return;
        }

        $conn = $this->resourceConnection->getConnection();
        $conn->update('quote_item', ['source_stock_id' => $sourceStockId, 'location_id' => $sourceStockId], "item_id = $quoteItemId");
    }
}
