<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Attribute\Config;
use Magento\Catalog\Model\ResourceModel\Category\Collection\Factory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Tree\Dbp;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tree extends Dbp
{
    const ID_FIELD = 'id';

    const PATH_FIELD = 'path';

    const ORDER_FIELD = 'order';

    const LEVEL_FIELD = 'level';
    /**
     * Categories resource collection
     *
     * @var Collection
     */
    protected $_collection;
    /**
     * Join URL rewrites data to collection flag
     *
     * @var boolean
     */
    protected $_joinUrlRewriteIntoCollection = false;
    /**
     * Inactive categories ids
     *
     * @var array
     */
    protected $_inactiveCategoryIds = null;
    /**
     * Store id
     *
     * @var integer
     */
    protected $_storeId = null;
    /**
     * @var ResourceConnection
     */
    protected $_coreResource;
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * Cache
     *
     * @var CacheInterface
     */
    protected $_cache;
    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $_catalogCategory;
    /**
     * @var MetadataPool
     */
    protected $metadataPool;
    /**
     * @var ManagerInterface
     */
    private $_eventManager;
    /**
     * @var Config
     */
    private $_attributeConfig;
    /**
     * @var Factory
     */
    private $_collectionFactory;

    /**
     * Tree constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Category $catalogCategory
     * @param CacheInterface $cache
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resource
     * @param ManagerInterface $eventManager
     * @param Config $attributeConfig
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Category $catalogCategory,
        CacheInterface $cache,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        ManagerInterface $eventManager,
        Config $attributeConfig,
        CollectionFactory $collectionFactory
    )
    {
        $this->_catalogCategory = $catalogCategory;
        $this->_cache = $cache;
        $this->_storeManager = $storeManager;
        $this->_coreResource = $resource;
        parent::__construct(
            $resource->getConnection(), //catalog
            $resource->getTableName('omnyfy_cms_category'),
            [
                Dbp::ID_FIELD => 'category_id',
                Dbp::PATH_FIELD => 'path',
                Dbp::ORDER_FIELD => 'position',
                //Dbp::LEVEL_FIELD => 'level'
            ]
        );
        $this->_eventManager = $eventManager;
        $this->_attributeConfig = $attributeConfig;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Return store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Add data to collection
     *
     * @param Collection $collection
     * @param boolean $sorted
     * @param array $exclude
     * @param boolean $toLoad
     * @param boolean $onlyActive
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addCollectionData(
        $collection = null,
        $sorted = false,
        $exclude = [],
        $toLoad = true,
        $onlyActive = false
    )
    {
        if ($collection === null) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }

        if (!is_array($exclude)) {
            $exclude = [$exclude];
        }

        $nodeIds = [];
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }
        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {
            $disabledIds = $this->_getDisabledIds($collection, $nodeIds);
            if ($disabledIds) {
                $collection->addFieldToFilter('category_id', ['nin' => $disabledIds]);
            }
            $collection->addAttributeToFilter('is_active', 1);
            //$collection->addAttributeToFilter('include_in_menu', 1);
        }

        if ($this->_joinUrlRewriteIntoCollection) {
            $collection->joinUrlRewrite();
            $this->_joinUrlRewriteIntoCollection = false;
        }

        if ($toLoad) {
            $collection->load();

            foreach ($collection as $category) {
                if ($this->getNodeById($category->getId())) {
                    $this->getNodeById($category->getId())->addData($category->getData());
                }
            }

            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }
        }

        return $this;
    }

    /**
     * Get categories collection
     *
     * @param boolean $sorted
     * @return Collection
     */
    public function getCollection($sorted = false)
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_getDefaultCollection($sorted);
        }
        return $this->_collection;
    }

    /**
     * Enter description here...
     *
     * @param Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        if ($this->_collection !== null) {
            $this->_clean($this->_collection);
        }
        $this->_collection = $collection;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param boolean $sorted
     * @return Collection
     */
    protected function _getDefaultCollection($sorted = false)
    {
        $this->_joinUrlRewriteIntoCollection = true;
        $collection = $this->_collectionFactory->create();
        //$attributes = $this->_attributeConfig->getAttributeNames('catalog_category');
        //$collection->addAttributeToSelect($attributes);

        if ($sorted) {
            if (is_string($sorted)) {
                // $sorted is supposed to be attribute name
                $collection->addFieldToSort($sorted);
            } else {
                $collection->addFieldToSort('title');
            }
        }

        return $collection;
    }

    /**
     * Return disable category ids
     *
     * @param Collection $collection
     * @param array $allIds
     * @return array
     */
    protected function _getDisabledIds($collection, $allIds)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_inactiveItems = $this->getInactiveCategoryIds();
        $this->_inactiveItems = array_merge($this->_getInactiveItemIds($collection, $storeId), $this->_inactiveItems);

        $disabledIds = [];

        foreach ($allIds as $id) {
            $parents = $this->getNodeById($id)->getPath();
            foreach ($parents as $parent) {
                if (!$this->_getItemIsActive($parent->getId(), $storeId)) {
                    $disabledIds[] = $id;
                    continue;
                }
            }
        }
        return $disabledIds;
    }

    /**
     * Retrieve inactive categories ids
     *
     * @return array
     */
    public function getInactiveCategoryIds()
    {
        if (!is_array($this->_inactiveCategoryIds)) {
            $this->_initInactiveCategoryIds();
        }

        return $this->_inactiveCategoryIds;
    }

    /**
     * Retrieve inactive categories ids
     *
     * @return $this
     */
    protected function _initInactiveCategoryIds()
    {
        $this->_inactiveCategoryIds = [];
        //$this->_eventManager->dispatch('catalog_category_tree_init_inactive_category_ids', ['tree' => $this]);
        return $this;
    }

    /**
     * Retrieve inactive category item ids
     *
     * @param Collection $collection
     * @param int $storeId
     * @return array
     */
    protected function _getInactiveItemIds($collection, $storeId)
    {
//        $linkField = $this->getMetadataPool()->getMetadata(CategoryInterface::class)->getLinkField();
//        $intTable = $this->_coreResource->getTableName('catalog_category_entity_int');
//
//        $select = $collection->getAllIdsSql()
//            ->joinInner(
//                ['d' => $intTable],
//                "e.{$linkField} = d.{$linkField}",
//                []
//            )->joinLeft(
//                ['c' => $intTable],
//                "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.{$linkField} = d.{$linkField}",
//                []
//            )->where(
//                'd.attribute_id = :attribute_id'
//            )->where(
//                'd.store_id = :zero_store_id'
//            )->where(
//                $this->_conn->getCheckSql('c.value_id > 0', 'c.value', 'd.value') . ' = :cond'
//            );
//
//        return $this->_conn->fetchCol(
//            $select,
//            [
//                'attribute_id' => $this->_catalogCategory->getIsActiveAttributeId(),
//                'store_id' => $storeId,
//                'zero_store_id' => 0,
//                'cond' => 0
//            ]
//        );
        return [];
    }

    /**
     * Check is category items active
     *
     * @param int $id
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function _getItemIsActive($id)
    {
        if (!in_array($id, $this->_inactiveItems)) {
            return true;
        }
        return false;
    }

    /**
     * Add inactive categories ids
     *
     * @param mixed $ids
     * @return $this
     */
    public function addInactiveCategoryIds($ids)
    {
        if (!is_array($this->_inactiveCategoryIds)) {
            $this->_initInactiveCategoryIds();
        }
        $this->_inactiveCategoryIds = array_merge($ids, $this->_inactiveCategoryIds);
        return $this;
    }

    /**
     * Executing parents move method and cleaning cache after it
     *
     * @param mixed $category
     * @param mixed $newParent
     * @param mixed $prevNode
     * @return void
     */
    public function move($category, $newParent, $prevNode = null)
    {
        $this->_catalogCategory->move($category->getId(), $newParent->getId());
        parent::move($category, $newParent, $prevNode);

        $this->_afterMove();
    }

    /**
     * Move tree after
     *
     * @return $this
     */
    protected function _afterMove()
    {
        //$this->_cache->clean([\Magento\Catalog\Model\Category::CACHE_TAG]);
        return $this;
    }

    /**
     * Load whole category tree, that will include specified categories ids.
     *
     * @param array $ids
     * @param bool $addCollectionData
     * @param bool $updateAnchorArticleCount
     * @return $this|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadByIds($ids, $addCollectionData = true, $updateAnchorArticleCount = true)
    {
        //$levelField = $this->_conn->quoteIdentifier('level');
        $pathField = $this->_conn->quoteIdentifier('path');
        // load first two levels, if no ids specified
        if (empty($ids)) {
            $select = $this->_conn->select()->from($this->_table, 'category_id');
            //->where($levelField . ' <= 2');
            $ids = $this->_conn->fetchCol($select);
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }

        // collect paths of specified IDs and prepare to collect all their parents and neighbours
        $select = $this->_conn->select()->from($this->_table, ['path'])->where('category_id IN (?)', $ids);
        //$where = [$levelField . '=0' => true];

        foreach ($this->_conn->fetchAll($select) as $item) {
            $pathIds = explode('/', $item['path']);
            //$level = (int)$item['level'];
//            while ($level > 0) {
//                $pathIds[count($pathIds) - 1] = '%';
//                $path = implode('/', $pathIds);
//                $where["{$levelField}={$level} AND {$pathField} LIKE '{$path}'"] = true;
//                array_pop($pathIds);
//                $level--;
//            }
        }
        $where = array_keys($where);

        // get all required records
        if ($addCollectionData) {
            $select = $this->_createCollectionDataSelect();
        } else {
            $select = clone $this->_select;
            $select->order($this->_orderField . ' ' . Select::SQL_ASC);
        }
        $select->where(implode(' OR ', $where));

        // get array of records and add them as nodes to the tree
        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }
        if ($updateAnchorArticleCount) {
            $this->_updateAnchorArticleCount($arrNodes);
        }
        $childrenItems = [];
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->addChildNodes($childrenItems, '', null);
        return $this;
    }

    /**
     * Obtain select for categories with attributes.
     * By default everything from entity table is selected
     * + name, is_active and is_anchor
     * Also the correct article_count is selected, depending on is the category anchor or not.
     *
     * @param bool $sorted
     * @param array $optionalAttributes
     * @return Select
     */
    protected function _createCollectionDataSelect($sorted = true, $optionalAttributes = [])
    {
        $meta = $this->getMetadataPool()->getMetadata(CategoryInterface::class);
        $linkField = $meta->getLinkField();

        $select = $this->_getDefaultCollection($sorted ? $this->_orderField : false)->getSelect();
        // add attributes to select
        $attributes = ['name', 'is_active', 'is_anchor'];
        if ($optionalAttributes) {
            $attributes = array_unique(array_merge($attributes, $optionalAttributes));
        }
        $resource = $this->_catalogCategory;
        foreach ($attributes as $attributeCode) {
            /* @var $attribute Attribute */
            $attribute = $resource->getAttribute($attributeCode);
            // join non-static attribute table
            /*
            if (!$attribute->getBackend()->isStatic()) {
                $tableDefault = sprintf('d_%s', $attributeCode);
                $tableStore = sprintf('s_%s', $attributeCode);
                $valueExpr = $this->_conn->getCheckSql(
                    "{$tableStore}.value_id > 0",
                    "{$tableStore}.value",
                    "{$tableDefault}.value"
                );

                $select->joinLeft(
                    [$tableDefault => $attribute->getBackend()->getTable()],
                    sprintf(
                        '%1$s.' . $linkField . '=e.' . $linkField .
                        ' AND %1$s.attribute_id=%2$d AND %1$s.store_id=%3$d',
                        $tableDefault,
                        $attribute->getId(),
                        \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ),
                    [$attributeCode => 'value']
                )->joinLeft(
                    [$tableStore => $attribute->getBackend()->getTable()],
                    sprintf(
                        '%1$s.' . $linkField . '=e.' . $linkField .
                        ' AND %1$s.attribute_id=%2$d AND %1$s.store_id=%3$d',
                        $tableStore,
                        $attribute->getId(),
                        $this->getStoreId()
                    ),
                    [$attributeCode => $valueExpr]
                );
            }*/
        }

        // count children articles qty plus self articles qty
        $categoriesTable = $this->_coreResource->getTableName('omnyfy_cms_category');
        $categoriesArticlesTable = $this->_coreResource->getTableName('omnyfy_cms_article_category');

        $subConcat = $this->_conn->getConcatSql(['e.path', $this->_conn->quote('/%')]);
        $subSelect = $this->_conn->select()->from(
            ['see' => $categoriesTable],
            null
        )->joinLeft(
            ['scp' => $categoriesArticlesTable],
            'see.category_id=scp.category_id',
            ['COUNT(DISTINCT scp.article_id)']
        )
            ->where(
                'see.category_id = e.category_id'
            )->orWhere(
                'see.path LIKE ?',
                $subConcat
            );
        $select->columns(['article_count' => $subSelect]); //article_count

        $subSelect = $this->_conn->select()->from(
            ['cp' => $categoriesArticlesTable],
            'COUNT(cp.article_id)'
        )->where(
            'cp.category_id = e.category_id'
        );

        $select->columns(['self_article_count' => $subSelect]); //self_article_count

        return $select;
    }

    /**
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()
                ->get('Magento\Framework\EntityManager\MetadataPool');
        }
        return $this->metadataPool;
    }

    /**
     * Replace articles count with self articles count, if category is non-anchor
     *
     * @param array &$data
     * @return void
     */
    protected function _updateAnchorArticleCount(&$data)
    {
        foreach ($data as $key => $row) {
            if (0 === (int)$row['is_anchor']) {
                $data[$key]['article_count'] = $row['self_article_count'];
            }
        }
    }

    /**
     * Load array of category parents
     *
     * @param string $path
     * @param bool $addCollectionData
     * @param bool $withRootNode
     * @return array
     */
    public function loadBreadcrumbsArray($path, $addCollectionData = true, $withRootNode = false)
    {
        $pathIds = explode('/', $path);
        if (!$withRootNode) {
            array_shift($pathIds);
        }
        $result = [];
        if (!empty($pathIds)) {
            if ($addCollectionData) {
                $select = $this->_createCollectionDataSelect(false);
            } else {
                $select = clone $this->_select;
            }
            $select->where(
                'e.category_id IN(?)',
                $pathIds
            )->order(
                $this->_conn->getLengthSql('e.path') . ' ' . Select::SQL_ASC
            );
            $result = $this->_conn->fetchAll($select);
            $this->_updateAnchorArticleCount($result);
        }
        return $result;
    }

    /**
     * Get real existing category ids by specified ids
     *
     * @param array $ids
     * @return array
     */
    public function getExistingCategoryIdsBySpecifiedIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $select = $this->_conn->select()->from($this->_table, ['category_id'])->where('category_id IN (?)', $ids);
        return $this->_conn->fetchCol($select);
    }

    /**
     * Clean unneeded collection
     *
     * @param Collection|array $object
     * @return void
     */
    protected function _clean($object)
    {
        if (is_array($object)) {
            foreach ($object as $obj) {
                $this->_clean($obj);
            }
        }
        unset($object);
    }
}