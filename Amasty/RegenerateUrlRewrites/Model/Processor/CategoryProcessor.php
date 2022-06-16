<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Model\Processor;

use Generator;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\Exception\NotFoundException;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class CategoryProcessor implements ProcessorInterface
{
    const COLLECTION_PAGE_SIZE = 1000;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryUrlRewriteGenerator
     */
    private $categoryUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    public function __construct(
        CollectionFactory $collectionFactory,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        UrlPersistInterface $urlPersist
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * @param int $storeId
     * @param array $categoryIds
     * @return Generator
     * @throws NotFoundException
     */
    public function process(int $storeId, array $categoryIds = []): Generator
    {
        $categoryCollection = $this->getCategoryCollection($categoryIds, $storeId);
        $totalSize = (int)$categoryCollection->getSize();
        if (!$totalSize) {
            throw new NotFoundException(__('Cannot regenerate url rewrites - categories not found'));
        }

        $pageCount = $categoryCollection->getLastPageNumber();
        $currentPage = 1;
        while ($currentPage <= $pageCount) {
            $categoryCollection->clear();
            $categoryCollection->setCurPage($currentPage);
            /** @var \Magento\Catalog\Model\Category $category */
            foreach ($categoryCollection as $category) {
                $error = '';
                $category->setStoreId($storeId);
                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $category->getId(),
                    UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::REDIRECT_TYPE => 0,
                    UrlRewrite::STORE_ID => $storeId,
                ]);
                try {
                    $error = '';
                    $urls = $this->categoryUrlRewriteGenerator->generate($category);
                    foreach ($urls as $url) {
                        $this->urlPersist->deleteByData([
                            UrlRewrite::REQUEST_PATH => $url->getRequestPath(),
                            UrlRewrite::STORE_ID => $url->getStoreId()
                        ]);
                    }

                    $this->urlPersist->replace($urls);
                } catch (\Exception $e) {
                    $error = __('Category %1 was not processed because of error', $category->getId());
                }

                yield ['entityId' => $category->getId(), 'error' => $error] => $totalSize;
            }

            $currentPage++;
        }
    }

    /**
     * Get category collection
     *
     * @param array $categoriesFilter
     * @param int $storeId
     * @return CategoryCollection
     */
    private function getCategoryCollection(array $categoriesFilter = [], int $storeId = 0): CategoryCollection
    {
        $categoryCollection = $this->collectionFactory->create();
        $categoryCollection->setStore($storeId)
            ->setStoreId($storeId)
            ->addAttributeToSelect(['url_path', 'url_key'])
            ->addFieldToFilter('level', ['gt' => '1'])
            ->setPageSize(self::COLLECTION_PAGE_SIZE);

        if (count($categoriesFilter) > 0) {
            $categoryCollection->addIdFilter($categoriesFilter);
        }

        return $categoryCollection;
    }
}
