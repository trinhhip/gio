<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Zend_Db_Adapter_Abstract;

/**
 * Cms category collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param null|Zend_Db_Adapter_Abstract $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $connection = null,
        AbstractDb $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
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
     * Add enable filter to collection
     * @return $this
     */
    public function addActiveFilter()
    {
        return $this->addFieldToFilter('is_active', 1);
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
     * Retrieve tree ordered categories
     * @return array
     */
    public function getTreeOrderedArray()
    {
        $tree = [];
        if ($childs = $this->getGroupedChilds()) {
            $this->_toTree(0, $childs, $tree);
        }
        return $tree;
    }

    /**
     * Retrieve gruped category childs
     * @return array
     */
    public function getGroupedChilds()
    {
        $childs = [];
        if (count($this)) {
            foreach ($this as $item) {
                $childs[$item->getParentId()][] = $item;
            }
        }
        return $childs;
    }

    /**
     * Auxiliary function to build tree ordered array
     * @return array
     */
    protected function _toTree($itemId, $childs, &$tree)
    {
        if ($itemId) {
            $tree[] = $this->getItemById($itemId);
        }

        if (isset($childs[$itemId])) {
            foreach ($childs[$itemId] as $i) {
                $this->_toTree($i->getId(), $childs, $tree);
            }
        }
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
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\Category', 'OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category');
        $this->_map['fields']['category_id'] = 'main_table.category_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues('category_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['cps' => $this->getTable('omnyfy_cms_category_store')])
                ->where('cps.category_id IN (?)', $items);
            $result = $connection->fetchPairs($select);
            if ($result) {
                foreach ($this as $item) {
                    $categoryId = $item->getData('category_id');
                    if (!isset($result[$categoryId])) {
                        continue;
                    }
                    if ($result[$categoryId] == 0) {
                        $stores = $this->_storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                    } else {
                        $storeId = $result[$item->getData('category_id')];
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_ids', [$result[$categoryId]]);
                }
            }

            if ($this->_storeId) {
                foreach ($this as $item) {
                    $item->setStoreId($this->_storeId);
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
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('omnyfy_cms_category_store')],
                'main_table.category_id = store_table.category_id',
                []
            )->group(
                'main_table.category_id'
            );
        }
        $this->setOrder('main_table.position', 'ASC');
        parent::_renderFiltersBefore();
    }

}
