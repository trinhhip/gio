<?php
namespace Omnyfy\VendorFeatured\Ui\Component\Product;
use Magento\Catalog\Model\Category as CategoryModel;

class CategoryOptions extends \Magento\Catalog\Ui\Component\Product\Form\Categories\Options
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory
     */
    protected $bannerCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory $bannerCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightBannerPlacement\CollectionFactory $bannerCollectionFactory
    ){
        parent::__construct($categoryCollectionFactory, $request);
        $this->registry = $registry;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     */
    protected function getCategoriesTree()
    {
        if ($this->categoriesTree === null) {
            $storeId = $this->request->getParam('store');
            /* @var $matchingNamesCollection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $matchingNamesCollection = $this->categoryCollectionFactory->create();

            $matchingNamesCollection->addAttributeToSelect('path')
                ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
                ->setStoreId($storeId);

            $shownCategoriesIds = [];

            /** @var \Magento\Catalog\Model\Category $category */
            foreach ($matchingNamesCollection as $category) {
                foreach (explode('/', $category->getPath()) as $parentId) {
                    $shownCategoriesIds[$parentId] = 1;
                }
            }

            /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $collection = $this->categoryCollectionFactory->create();

            $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
                ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
                ->setStoreId($storeId);

            $categoryById = [
                CategoryModel::TREE_ROOT_ID => [
                    'value' => CategoryModel::TREE_ROOT_ID
                ],
            ];

            $placedCategory = $this->getPlacedCategory();
            foreach ($collection as $category) {
                foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                    if (!isset($categoryById[$categoryId])) {
                        $categoryById[$categoryId] = ['value' => $categoryId];
                    }
                }

                $search = array_search($category->getId(), array_column($placedCategory, 'category_id'));
                if ($search !== false) {
                    $categoryById[$category->getId()]['label'] = $category->getName(). " [Placement already assigned to \"" . $placedCategory[$search]['banner_name'] ."\"]";
                    $categoryById[$category->getId()]['disabled'] = true;
                }else{
                    $categoryById[$category->getId()]['label'] = $category->getName();
                }
                $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
                $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
            }

            $this->categoriesTree = $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
        }
        return $this->categoriesTree;
    }

    /**
     * Retrieve placed categories
     *
     * @return array
     */
    public function getPlacedCategory()
    {
        $placedCategory = [];

        $currentBanner = $this->registry->registry('omnyfy_vendorfeatured_spotlight_banner');
        if (isset($currentBanner) && $currentBanner!=null) {
            $bannerId = $currentBanner->getBannerId();
            $banners = $this->bannerCollectionFactory->create()
                ->addFieldToFilter('banner_id', array('neq' => $bannerId))
                ->addFieldToFilter('category_ids', array('notnull' => true))
            ;
            if (count($banners)) {
                foreach ($banners as $banner) {
                    $categoryIds = $banner->getCategoryIds();
                    if (strpos($categoryIds, ",") !== false) {
                        $ids = explode(",", $categoryIds);
                        foreach ($ids as $id) {
                            $placedCategory[] = [
                                'category_id' => $id,
                                'banner_name' => $banner->getBannerName()
                            ];
                        }
                    }else{
                        $placedCategory[] = [
                            'category_id' => $categoryIds,
                            'banner_name' => $banner->getBannerName()
                        ];
                    }
                }
            }
        }
        return $placedCategory;
    }
}