<?php


namespace OmnyfyCustomzation\B2C\ViewModel;


use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Wishlist extends DataObject implements ArgumentInterface
{
    /**
     * @var StockRegistryInterface
     */
    public $stockRegistry;

    public function __construct(
        StockRegistryInterface $stockRegistry,
        $data = []
    )
    {
        $this->stockRegistry = $stockRegistry;
        parent::__construct($data);
    }

    public function getStock($productId)
    {
        return $this->stockRegistry->getStockItem($productId);
    }
}
