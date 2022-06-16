<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Model\Processor;

use Generator;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Exception\NotFoundException;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class ProductProcessor implements ProcessorInterface
{
    const COLLECTION_PAGE_SIZE = 1000;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductUrlRewriteGenerator
     */
    private $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    public function __construct(
        CollectionFactory $collectionFactory,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @return Generator
     * @throws NotFoundException
     */
    public function process(int $storeId, array $productIds = []): Generator
    {
        $productCollection = $this->getProductCollection($productIds, $storeId);
        $totalSize = (int)$productCollection->getSize();
        if (!$totalSize) {
            throw new NotFoundException(__('Cannot regenerate url rewrites - products not found'));
        }

        $pageCount = $productCollection->getLastPageNumber();
        $currentPage = 1;
        $messages = [];
        while ($currentPage <= $pageCount) {
            $productCollection->clear();
            $productCollection->setCurPage($currentPage);
            /** @var \Magento\Catalog\Model\Product $product */
            foreach ($productCollection as $product) {
                $product->setStoreId($storeId);
                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::REDIRECT_TYPE => 0,
                    UrlRewrite::STORE_ID => $storeId,
                ]);

                try {
                    $error = '';
                    $product->setData('url_path', null);
                    $urls = $this->productUrlRewriteGenerator->generate($product);
                    foreach ($urls as $url) {
                        $this->urlPersist->deleteByData([
                            UrlRewrite::REQUEST_PATH => $url->getRequestPath(),
                            UrlRewrite::STORE_ID => $url->getStoreId()
                        ]);
                    }

                    $this->urlPersist->replace($urls);
                } catch (\Exception $e) {
                    $error = __('Product %1 was not processed because of error', $product->getId());
                }

                yield ['entityId' => $product->getId(), 'error' => $error] => $totalSize;
            }

            $currentPage++;
        }

        return $messages;
    }

    /**
     * Get product collection
     *
     * @param array $productsFilter
     * @param int $storeId
     * @return ProductCollection
     */
    private function getProductCollection(array $productsFilter = [], int $storeId = 0): ProductCollection
    {
        $productCollection = $this->collectionFactory->create();
        $productCollection->setStore($storeId)
            ->addStoreFilter($storeId)
            ->setStoreId($storeId)
            ->addAttributeToSelect(['url_path', 'url_key'])
            ->setPageSize(self::COLLECTION_PAGE_SIZE);

        if (count($productsFilter) > 0) {
            $productCollection->addIdFilter($productsFilter);
        }

        return $productCollection;
    }
}
