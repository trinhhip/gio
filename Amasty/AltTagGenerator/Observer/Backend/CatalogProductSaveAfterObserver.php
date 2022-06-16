<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Observer\Backend;

use Amasty\AltTagGenerator\Model\Indexer\Template\ProductProcessor;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    public function __construct(
        ProductResource $productResource,
        ProductProcessor $productProcessor
    ) {
        $this->productResource = $productResource;
        $this->productProcessor = $productProcessor;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if ($product) {
            $this->productResource->addCommitCallback(function () use ($product) {
                $this->productProcessor->reindexRow((int) $product->getEntityId());
            });
        }
    }
}
