<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\CmsBlog\Model\Author;
use OmnyfyCustomzation\CmsBlog\Model\Category;
use OmnyfyCustomzation\CmsBlog\Model\Tag;
use OmnyfyCustomzation\CmsBlog\Model\UserType;
use Psr\Log\LoggerInterface;
use Zend_Db_Adapter_Abstract;

/**
 * Cms article collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param DateTime $date
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null|Zend_Db_Adapter_Abstract $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateTime $date,
        StoreManagerInterface $storeManager,
        $connection = null,
        AbstractDb $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->_date = $date;
        $this->_storeManager = $storeManager;
    }

    /**
     * Add category filter to collection
     * @param array|int|Category $category
     * @return $this
     */
    public function addCategoryFilter($category)
    {
        if (!$this->getFlag('category_filter_added')) {
            if ($category instanceof Category) {
                $category = [$category->getId()];
            }

            if (!is_array($category)) {
                $category = [$category];
            }

            $this->addFilter('category', ['in' => $category], 'public');
            $this->setOrder('category_table.position', 'ASC');
        }
        return $this;
    }

    /**
     * Add tag filter to collection
     * @param array|int|Tag $tag
     * @return $this
     */
    public function addTagFilter($tag)
    {
        if (!$this->getFlag('tag_filter_added')) {
            if ($tag instanceof Tag) {
                $tag = [$tag->getId()];
            }

            if (!is_array($tag)) {
                $tag = [$tag];
            }

            $this->addFilter('tag', ['in' => $tag], 'public');
        }
        return $this;
    }

    /**
     * Add userType filter to collection
     * @param array|int|UserType $userType
     * @return $this
     */
    public function addUserTypeFilter($userType)
    {
        if (!$this->getFlag('userType_filter_added')) {
            if ($userType instanceof UserType) {
                $userType = [$userType->getId()];
            }

            if (!is_array($userType)) {
                $userType = [$userType];
            }

            $this->addFilter('userType', ['in' => $userType], 'public');
        }
        return $this;
    }

    /**
     * Add author filter to collection
     * @param array|int|Author $author
     * @return $this
     */
    public function addAuthorFilter($author)
    {
        if (!$this->getFlag('author_filter_added')) {
            if ($author instanceof Author) {
                $author = [$author->getId()];
            }

            if (!is_array($author)) {
                $author = [$author];
            }

            $this->addFilter('author_id', ['in' => $author], 'public');
        }
        return $this;
    }

    /**
     * Add is_active filter to collection
     * @return $this
     */
    public function addActiveFilter()
    {
        return $this
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('publish_time', ['lteq' => $this->_date->gmtDate()]);
    }

    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id' || $field === 'store_ids') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add store filter to collection
     * @param array|int|Store $store
     * @param boolean $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store === null) {
            return $this;
        }

        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Store) {
                $this->_storeId = $store->getId();
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $this->_storeId = $store;
                $store = [$store];
            }

            if (in_array(Store::DEFAULT_STORE_ID, $store)) {
                return $this;
            }

            if ($withAdmin) {
                $store[] = Store::DEFAULT_STORE_ID;
            }

            $this->addFilter('store', ['in' => $store], 'public');
        }
        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Select::GROUP);

        return $countSelect;
    }

    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\Article', 'OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article');
        $this->_map['fields']['article_id'] = 'main_table.article_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['category'] = 'category_table.category_id';
        $this->_map['fields']['tag'] = 'tag_table.tag_id';
        $this->_map['fields']['userType'] = 'userType_table.id';
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues('article_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $tableName = $this->getTable('omnyfy_cms_article_store');
            $select = $connection->select()
                ->from(['cps' => $tableName])
                ->where('cps.article_id IN (?)', $items);

            $result = $connection->fetchPairs($select);
            if ($result) {
                foreach ($this as $item) {
                    $articleId = $item->getData('article_id');
                    if (!isset($result[$articleId])) {
                        continue;
                    }
                    if ($result[$articleId] == 0) {
                        $stores = $this->_storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                    } else {
                        $storeId = $result[$item->getData('article_id')];
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_ids', [$result[$articleId]]);
                }
            }

            if ($this->_storeId) {
                foreach ($this as $item) {
                    $item->setStoreId($this->_storeId);
                }
            }

            $map = [
                'category' => 'categories',
                'tag' => 'tags',
                'user_type' => 'user_types',
            ];

            foreach ($map as $key => $property) {
                $tableName = $this->getTable('omnyfy_cms_article_' . $key);
                $select = $connection->select()
                    ->from(['cps' => $tableName])
                    ->where('cps.article_id IN (?)', $items);

                $result = $connection->fetchAll($select);
                if ($result) {
                    $data = [];
                    foreach ($result as $item) {
                        $data[$item['article_id']][] = $item[$key . '_id'];
                    }

                    foreach ($this as $item) {
                        $articleId = $item->getData('article_id');
                        if (isset($data[$articleId])) {
                            $item->setData($property, $data[$articleId]);
                        }
                    }
                }
            }
        }

        $this->_previewFlag = false;
        return parent::_afterLoad();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        foreach (['store', 'category', 'tag', 'user_type'] as $key) {
            if ($this->getFilter($key)) {
                $this->getSelect()->join(
                    [$key . '_table' => $this->getTable('omnyfy_cms_article_' . $key)],
                    'main_table.article_id = ' . $key . '_table.article_id',
                    []
                )->group(
                    'main_table.article_id'
                );
            }
        }
        parent::_renderFiltersBefore();
    }

}
