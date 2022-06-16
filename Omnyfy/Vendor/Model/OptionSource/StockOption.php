<?php

namespace Omnyfy\Vendor\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Inventory\Model\ResourceModel\Stock\CollectionFactory;

class StockOption implements OptionSourceInterface
{
    protected $stockCollectionFactory;

    public function __construct(
        CollectionFactory $stockCollectionFactory
    ) {
        $this->stockCollectionFactory = $stockCollectionFactory;
    }
    public function toOptionArray()
    {
        $stockOptions = [];
        $stocks = $this->stockCollectionFactory->create()->getItems();
        foreach ($stocks as $stock) {
            if ($stock->getId() == 1) {
                continue;
            }
            $stockOptions[] = [
                'value' => $stock->getId(),
                'label' => $stock->getname()
            ];
        }
        return $stockOptions;
    }

}
