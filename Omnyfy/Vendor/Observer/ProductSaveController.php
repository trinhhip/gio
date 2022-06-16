<?php

namespace Omnyfy\Vendor\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductSaveController  implements ObserverInterface
{
    protected $inventoryFactory;
    protected $inventoryCollectionFactory;
    protected $vSourceStockResource;
    protected $inventoryResource;
    protected $productTypeConfigurable;
    protected $productCollectionFactory;

    public function __construct(
        \Omnyfy\Vendor\Model\InventoryFactory $inventoryFactory,
        \Omnyfy\Vendor\Model\Resource\Inventory\CollectionFactory $collectionFactory,
        \Omnyfy\Vendor\Model\Resource\Inventory $inventoryResource,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->inventoryFactory = $inventoryFactory;
        $this->inventoryCollectionFactory = $collectionFactory;
        $this->inventoryResource = $inventoryResource;
        $this->vSourceStockResource = $vSourceStockResource;
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function execute(Observer $observer)
    {
        $controller = $observer->getController();
        $sku = $observer->getProduct()->getSku();
        $productId = $observer->getProduct()->getId();
        $postData = $controller->getRequest()->getPostValue();
        $productTypeId = $observer->getProduct()->getTypeId();
        $productCollection = $this->productCollectionFactory->create();
        $parentIds = $this->productTypeConfigurable->getParentIdsByChild($productId);
        $isChildProduct = false;
        if (!empty($parentIds)) {
            $isChildProduct = true;
        }
        if (isset($postData['sources']['assigned_sources'])) {
            $dataSources = $postData['sources']['assigned_sources'];
            $oldSourceCodes = $this->getItemsInInventory($sku);
            $newSourceCode = [];
            foreach ($dataSources as $source) {
                $newSourceCode[] = $source['source_code'];
            }
            if (!empty($oldSourceCodes) && !empty($newSourceCode)) {
                foreach ($oldSourceCodes as $sourceCode) {
                    if (!in_array($sourceCode, $newSourceCode)) {
                        $this->inventoryResource->removeBySourceCode($sourceCode, $sku);
                        /* If is child product of configurable product
                             * After delete row from table then check
                             * If Configurable has no child Product in table omnyfy_vendor_inventory, delete configurable product
                             */
                        if ($isChildProduct) {
                            foreach ($parentIds as $parentId) {
                                if ($this->inventoryResource->isNoChildProduct($parentId, $sourceCode)) {
                                    $this->inventoryResource->removeByProducIdAndSourceCode($parentId, $sourceCode);
                                }
                            }
                        }
                    }
                }
            }

            foreach ($dataSources as $source) {
                $sourceStockIds = $this->vSourceStockResource->getIdsBySourceCode($source['source_code']);
                foreach ($sourceStockIds as $id) {
                    if ($this->isSelected($id, $productId)) {
                        $this->inventoryResource->updateQtyBySourceCode($source['source_code'], $sku, $productId, $id, $source['quantity']);
                    } else {
                        $dataSave = [
                            'product_id' => $productId,
                            'source_code' => $source['source_code'],
                            'sku' => $sku,
                            'quantity' => $source['quantity'],
                            'source_stock_id' => $id
                        ];
                        $this->inventoryResource->saveDuplicateData($dataSave);
                    }
                }
            }
        } else {
            $dataRemove = ['sku' => $sku];
            $oldSourceCodes = $this->getItemsInInventory($sku);
            $this->inventoryResource->remove($dataRemove, $this->inventoryResource->getMainTable());
            if ($isChildProduct) {
                foreach ($parentIds as $parentId) {
                    foreach ($oldSourceCodes as $sourceCode) {
                        if ($this->inventoryResource->isNoChildProduct($parentId, $sourceCode)) {
                            $this->inventoryResource->removeByProducIdAndSourceCode($parentId, $sourceCode);
                        }
                    }
                }
            }
        }
    }

    protected function isSelected($id, $productId)
    {
        $collection = $this->inventoryCollectionFactory->create();
        $item = $collection->addFieldToFilter('product_id', $productId)->addFieldToFilter('source_stock_id', $id)->getFirstItem();
        if ($item->getData()) {
            return true;
        } else {
            return false;
        }
    }

    protected function getItemsInInventory($sku)
    {
        $sourceCodes = [];
        $collection = $this->inventoryCollectionFactory->create();
        $items = $collection->addFieldToFilter('sku', $sku)->getItems();

        if ($items) {
            foreach ($items as $item) {
                $sourceCodes[] = $item->getSourceCode();
            }
        }

        return $sourceCodes;
    }
}
