<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml\Category;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use OmnyfyCustomzation\CmsBlog\Model\Category as Category;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;

/**
 * Class AbstractCategory
 */
class AbstractCategory extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    protected $_categoryTree;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var bool
     */
    protected $_withProductCount;

    /**
     * @param Context $context
     * @param \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\Tree $categoryTree
     * @param Registry $registry
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\Tree $categoryTree,
        Registry $registry,
        CategoryFactory $categoryFactory,
        array $data = []
    )
    {
        $this->_categoryTree = $categoryTree;
        $this->_coreRegistry = $registry;
        $this->_categoryFactory = $categoryFactory;
        $this->_withProductCount = true;
        parent::__construct($context, $data);
    }

    /**
     * @return int|string|null
     */
    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }
        return 0;
    }

    /**
     * Retrieve current category instance
     *
     * @return array|null
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('cms_category');
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }

    /**
     * @return mixed
     */
    public function getCategoryPath()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getPath();
        }
        return Category::TREE_ROOT_ID;
    }

    /**
     * @return bool
     */
    public function hasStoreRootCategory()
    {
        $root = $this->getRoot();
        if ($root && $root->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @param mixed|null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|array|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if ($parentNodeCategory !== null && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = $this->_coreRegistry->registry('root');
        if ($root === null) {
            $storeId = (int)$this->getRequest()->getParam('store');

            if ($storeId) {
                $store = $this->_storeManager->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            } else {
                $rootId = Category::TREE_ROOT_ID;
            }

            $tree = $this->_categoryTree->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $this->_coreRegistry->register('root', $root);
        }

        return $root;
    }

    /**
     * @param mixed $parentNodeCategory
     * @param int $recursionLevel
     * @return Node
     */
    public function getNode($parentNodeCategory, $recursionLevel = 2)
    {
        $nodeId = $parentNodeCategory->getId();
        $node = $this->_categoryTree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == Category::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $this->_categoryTree->addCollectionData($this->getCategoryCollection());

        return $node;
    }

    /**
     * @return AbstractCollection
     */
    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if ($collection === null) {
            $collection = $this->_categoryFactory->create()->getCollection();

            $collection
//                    ->addAttributeToSelect(
//                'name'
//            )->addAttributeToSelect(
//                'is_active'
//            )->setProductStoreId(
//                $storeId
//            )->setLoadProductCount(
//                $this->_withProductCount
//            )
                ->setStoreId($storeId);

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    /**
     * @return int
     */
    protected function _getDefaultStoreId()
    {
        return Store::DEFAULT_STORE_ID;
    }

    /**
     * Get and register categories root by specified categories IDs
     *
     * IDs can be arbitrary set of any categories ids.
     * Tree with minimal required nodes (all parents and neighbours) will be built.
     * If ids are empty, default tree with depth = 2 will be returned.
     *
     * @param array $ids
     * @return mixed
     */
    public function getRootByIds($ids)
    {
        $root = $this->_coreRegistry->registry('root');
        if (null === $root) {
            $ids = $this->_categoryTree->getExistingCategoryIdsBySpecifiedIds($ids);
            $tree = $this->_categoryTree->loadByIds($ids);
            $rootId = Category::TREE_ROOT_ID;
            $root = $tree->getNodeById($rootId);
            if ($root && $rootId != Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $tree->addCollectionData($this->getCategoryCollection());
            $this->_coreRegistry->register('root', $root);
        }
        return $root;
    }

    /**
     * @param array $args
     * @return string
     */
    public function getSaveUrl(array $args = [])
    {
        $params = ['_current' => false, '_query' => false, 'store' => $this->getStore()->getId()];
        $params = array_merge($params, $args);
        return $this->getUrl('catalog/*/save', $params);
    }

    /**
     * @return Store
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            'catalog/category/edit',
            ['store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     */
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if ($ids === null) {
            $ids = [Category::TREE_ROOT_ID];
            foreach ($this->_storeManager->getGroups() as $store) {
                $ids[] = $store->getRootCategoryId();
            }
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
}
