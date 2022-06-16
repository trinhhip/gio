<?php

namespace Omnyfy\Vendor\Model\Layer\Filter;

use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection as ShopbyFulltextCollection;
use Amasty\Shopby\Model\Source\RenderCategoriesLevel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Search\Api\SearchInterface;
use Amasty\Shopby\Model\Layer\Filter\Category as AmastyCategory;

class Category extends AmastyCategory
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var SearchEngine
     */
    private $searchEngine;
    /**
     * @var \Amasty\ShopbyBase\Model\Category\Manager
     */
    private $categoryManager;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        \Amasty\Shopby\Helper\FilterSetting $settingHelper,
        \Amasty\ShopbyBase\Model\Category\Manager $categoryManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Amasty\Shopby\Model\Layer\Filter\Item\CategoryExtendedDataBuilder $categoryExtendedDataBuilder,
        \Amasty\Shopby\Model\Layer\Filter\CategoryItemsFactory $categoryItemsFactory,
        \Amasty\Shopby\Helper\Data $helper,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        \Amasty\Shopby\Helper\Category $categoryHelper,
        SearchInterface $searchEngine,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $categoryFactory,
            $escaper,
            $categoryDataProviderFactory,
            $settingHelper,
            $categoryManager,
            $categoryRepository,
            $categoryExtendedDataBuilder,
            $categoryItemsFactory,
            $helper,
            $shopbyRequest,
            $categoryHelper,
            $searchEngine,
            $messageManager,
            $productMetadata,
            $logger,
            $data
        );
        $this->registry = $registry;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->searchEngine = $searchEngine;
        $this->categoryManager = $categoryManager;
        $this->categoryRepository = $categoryRepository;
    }

    protected function getFacetedData()
    {
        $optionsFacetedData = [];
        if ($this->tryCategoryBucket()) {
            $currentCat =  $this->registry->registry('current_category');
            $this->getLayer()->setData('current_category',null);
            $this->registry->unregister('current_category');
            $productCollection = $this->getProductCollection();
            $optionsFacetedData = $productCollection->getFacetedData(
                self::FILTER_FIELD,
                $this->getAlteredQueryResponse()
            );
            $this->registry->register('current_category',$currentCat);
            $this->getLayer()->setData('current_category',$currentCat);
        }

        return $optionsFacetedData;
    }

    /**
     * Check is current filter has results
     *
     * @return bool
     */
    private function tryCategoryBucket()
    {
        $productCollection = $this->getProductCollection();
        if (!($productCollection instanceof ShopbyFulltextCollection)) {
            //fix fatal with Call to undefined method getMemRequestBuilder()
            $this->messageManager->addErrorMessage(
                __('Something went wrong during rendering of navigation filters. Please try again later.')
            );
            $this->logger->error(
                __('Something went wrong during rendering of navigation filters. Please try again later.')
            );
            return false;
        }

        $alteredQueryResponse = $this->searchEngine->search($this->buildQueryRequest($this->getCurrentCategoryId()));
        try {
            $productCollection->getFacetedData('category', $alteredQueryResponse);
        } catch (StateException $e) {
            $this->catchBucketException();
            return false;
        }

        return true;
    }


    /**
     * @return \Amasty\Shopby\Model\Layer\Filter\Category
     */
    private function catchBucketException()
    {
        if (is_array($this->currentValue)) {
            $categoryId = current($this->currentValue);
            try {
                $category = $this->categoryRepository->get(
                    $categoryId,
                    $this->categoryManager->getCurrentStoreId()
                );
            } catch (NoSuchEntityException $e) {
                $category = $this->getRootCategory();
            }
        } else {
            $category = $this->getRootCategory();
        }

        $this->messageManager->addErrorMessage(
            __(
                'Make sure that "%1"(id:%2) category for current store is anchored',
                $category->getName(),
                $category->getId()
            )
        );

        return $this;
    }

    /**
     * Retrieve root category for current store
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     */
    private function getRootCategory()
    {
        if (!$this->getData('root_category')) {
            $category = $this->categoryRepository->get(
                $this->categoryManager->getRootCategoryId(),
                $this->categoryManager->getCurrentStoreId()
            );
            $this->setData('root_category', $category);
        }

        return $this->getData('root_category');
    }

    /**
     * @return \Magento\Framework\Search\ResponseInterface|null
     */
    private function getAlteredQueryResponse()
    {
        $alteredQueryResponse = null;

        $isCurrentLevel = $this->getRenderCategoriesLevel() == RenderCategoriesLevel::CURRENT_CATEGORY_LEVEL;
        $isRootLevel = $this->getRenderCategoriesLevel() == RenderCategoriesLevel::ROOT_CATEGORY;
        $excludeCurrentLevel = $isCurrentLevel || $isRootLevel || $this->isRenderAllTree();

        if ($this->hasCurrentValue() || ($excludeCurrentLevel && $this->isMultiselect())) {
            $categoryId = $this->getCategoryIdByLevel($isCurrentLevel);
            $alteredQueryResponse = $this->searchEngine->search($this->buildQueryRequest($categoryId));
        }

        return $alteredQueryResponse;
    }
}
