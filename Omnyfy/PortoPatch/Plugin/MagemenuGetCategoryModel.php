<?php

namespace Omnyfy\PortoPatch\Plugin;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Smartwave\Megamenu\Block\Topmenu;

class MagemenuGetCategoryModel
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $categoryCollection;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    private $categoryMapping = [];

    public function __construct(
        CollectionFactory $categoryCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryCollection = $categoryCollection;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Topmenu $subject
     * @param callable $proceed
     * @param $id
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetCategoryModel(Topmenu $subject, callable $proceed, $id)
    {
        if (empty($this->categoryMapping)) {
            $collection = $this->categoryCollection->create()
                ->addAttributeToSelect('*')
                ->setStore($this->storeManager->getStore())
                ->addAttributeToFilter('is_active','1');
            foreach ($collection as $category) {
                $this->categoryMapping[$category->getId()] = $category;
            }
        }

        if (isset($this->categoryMapping[$id])) {
            return $this->categoryMapping[$id];
        }

        return $proceed($id);
    }
}