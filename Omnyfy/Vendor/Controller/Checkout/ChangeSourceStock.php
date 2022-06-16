<?php
namespace Omnyfy\Vendor\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class ChangeSourceStock extends \Magento\Framework\App\Action\Action
{
    protected $resultFactory;
    protected $quoteRepository;
    protected $stockItemRepository;
    protected $stockManager;
    protected $vSourceStockCollectionFactory;
    protected $inventoryResource;
    protected $inventoryAddressFactory;
    protected $sourceRepository;
    protected $getDistanceFromSourceToAddress;
    protected $getAssignedStockIdForWebsite;
    protected $productRepository;
    protected $isProductSalableForRequestedQty;
    protected $getStockItemConfiguration;
    protected $isProductSaleableForSource;

    public function __construct(
        Context $context,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Store\Model\StoreManagerInterface $stockManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock\CollectionFactory $vSourceStockCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\Inventory $inventoryResource,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\InventorySourceSelectionApi\Api\Data\AddressInterfaceFactory $inventoryAddressFactory,
        \Magento\Inventory\Model\SourceRepository $sourceRepository,
        \Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GetDistanceFromSourceToAddress $getDistanceFromSourceToAddress,
        \Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface $getStockItemConfiguration,
        \Omnyfy\Vendor\Model\IsProductSaleableForSource $isProductSaleableForSource
    ) {
        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockManager = $stockManager;
        $this->resultFactory = $resultFactory;
        $this->vSourceStockCollectionFactory = $vSourceStockCollectionFactory;
        $this->inventoryResource = $inventoryResource;
        $this->_checkoutSession = $_checkoutSession;
        $this->inventoryAddressFactory = $inventoryAddressFactory;
        $this->sourceRepository = $sourceRepository;
        $this->getDistanceFromSourceToAddress = $getDistanceFromSourceToAddress;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
        $this->productRepository = $productRepository;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->isProductSaleableForSource = $isProductSaleableForSource;
    }

    public function execute()
    {
        $quote = $this->_checkoutSession->getQuote();
        $items = $quote->getAllItems();
        $idsToFilter = [];
        $arrProductAndSourceStockId = [];
        $arrCheckIsChangedAdress = [];
        $addressData = $this->getRequest()->getParam('shipping_address');

        foreach ($items as $item){
            $requestQty = $item->getQty();
            $websiteCode = $this->stockManager->getWebsite()->getCode();
            $websiteId = $this->stockManager->getWebsite()->getId();
            $stockId = $this->getAssignedStockIdForWebsite->execute($websiteCode);
            $sku = $this->productRepository->getById($item->getProductId())->getSku();
            $vendorId = $item->getVendorId();
            $qtys = $this->inventoryResource->loadInventoryGroupedByLocation($item->getProductId(), $websiteId, $vendorId);
            $distanceBySourceCode = [];
            $sourceCodeBySourceStockId = [];
            if (count($qtys) > 0) {
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
                        }
                    }
                    asort($distanceBySourceCode);
                    foreach ($distanceBySourceCode as $id => $distance) {
                        if (!$this->getStockItemConfiguration->execute($sku, $stockId)->isManageStock()) {
                            $idsToFilter[] = $id;
                            if ($item->getParentItemId()) {
                                $arrProductAndSourceStockId[$item->getParentItem()->getProductId()] = $id;
                                break;
                            }
                            $arrProductAndSourceStockId[$item->getproductId()] = $id;
                            break;
                        }
                        if ($this->isProductSaleableForSource->execute($sku, $stockId, $sourceCodeBySourceStockId[$id] ,$requestQty)) {
                            $idsToFilter[] = $id;
                            if ($item->getParentItemId()) {
                                $arrProductAndSourceStockId[$item->getParentItem()->getProductId()] = $id;
                                break;
                            }
                            $arrProductAndSourceStockId[$item->getproductId()] = $id;
                            break;
                        }
                    }
                } else {
                    $idsToFilter[] = $item->getSourceStockId();
                    if ($item->getParentItemId()) {
                        $arrProductAndSourceStockId[$item->getParentItem()->getProductId()] = $item->getSourceStockId();
                    } else {
                        $arrProductAndSourceStockId[$item->getproductId()] = $item->getSourceStockId();
                    }
                }
            }
        }
        $count = 0;
        foreach ($arrCheckIsChangedAdress as $productId => $sourceId) {
            if ($sourceId == $arrProductAndSourceStockId[$productId]) {
                $count++;
            }
        }
        $vSourceStockCollection = $this->vSourceStockCollectionFactory->create()->addFieldToFilter('id', ['in' => $idsToFilter]);
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['source_stock' => $vSourceStockCollection->getData(), 'product_change' => $arrProductAndSourceStockId]);
        return $response;
    }
}
