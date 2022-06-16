<?php

/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;
use OmnyfyCustomzation\CmsBlog\Model\ArticleFactory;
use Zend_Db_Select;

/**
 * Cms category resource model
 */
class Category extends AbstractDb
{

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param Context $context
     * @param DateTime $dateTime
     * @param string|null $resourcePrefix
     */
    public function __construct(
        Context $context, DateTime $dateTime, ArticleFactory $articleFactory, $resourcePrefix = null
    )
    {
        parent::__construct($context, $resourcePrefix);
        $this->dateTime = $dateTime;
        $this->articleFactory = $articleFactory; //
    }

    public function updateArticlePosition($data)
    {
        if (!empty($data)) {
            $articleModel = $this->articleFactory->create();
            foreach ($data as $key => $row) {
                $articleModel->load($key);
                $articleModel->setData('article_counter_update', 1);
                $articleModel->setData('position', $row['position']);
                $articleModel->save();
            }
        }
        return;
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Check if category identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int|array $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $storeIds[] = Store::DEFAULT_STORE_ID;
        $select = $this->_getLoadByIdentifierSelect($identifier, $storeIds, 1);
        $select->reset(Zend_Db_Select::COLUMNS)->columns('cp.category_id')->order('cps.store_id DESC')->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Check if category identifier exist for specific store
     * return category id if category exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    protected function _getLoadByIdentifierSelect($identifier, $storeIds, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('omnyfy_cms_category_store')], 'cp.category_id = cps.category_id', []
        )->where(
            'cp.identifier = ?', $identifier
        )->where(
            'cps.store_id IN (?)', $storeIds
        );

        if (!is_null($isActive)) {
            $select->where('cp.is_active = ?', $isActive);
        }
        return $select;
    }

    /**
     * Check if category identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int|array $storeId
     * @return int
     */
    public function checkIdentifierCount($identifier, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        $storeIds[] = Store::DEFAULT_STORE_ID;
        $select = $this->_getLoadByIdentifierSelectCount($identifier, $storeIds, 1);
        $select->reset(Zend_Db_Select::COLUMNS)->columns('cp.category_id')->order('cps.store_id DESC');
        #echo $select;exit;
        return count($this->getConnection()->fetchAll($select)) + 1;
    }

    /**
     * Check if category identifier exist for specific store
     * return category id if category exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    protected function _getLoadByIdentifierSelectCount($identifier, $storeIds, $isActive = null)
    {
        if ($identifier) {
            $identifier = '%' . $identifier . '%';
        }

        $select = $this->getConnection()->select()->from(
            ['cp' => $this->getMainTable()]
        )->join(
            ['cps' => $this->getTable('omnyfy_cms_category_store')], 'cp.category_id = cps.category_id', []
        )->where(
            'cp.identifier LIKE ?', $identifier
        )->where(
            'cps.store_id IN (?)', $storeIds
        );

        if (!is_null($isActive)) {
            $select->where('cp.is_active = ?', $isActive);
        }
        return $select;
    }

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_cms_category', 'category_id');
    }

    /**
     * Process category data before deleting
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['category_id = ?' => (int)$object->getId()];

        $this->getConnection()->delete($this->getTable('omnyfy_cms_category_store'), $condition);
        $this->getConnection()->delete($this->getTable('omnyfy_cms_article_category'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process category data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        foreach (['custom_theme_from', 'custom_theme_to'] as $field) {
            $value = $object->getData($field) ?: null;
            $object->setData($field, $this->dateTime->formatDate($value));
        }

        $identifierGenerator = ObjectManager::getInstance()
            ->create('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\PageIdentifierGenerator');
        $identifierGenerator->generate($object);

        if (!$this->isValidPageIdentifier($object)) {
            throw new LocalizedException(
                __('The category URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new LocalizedException(
                __('The category URL key cannot be made of only numbers.')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     *  Check whether category identifier is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    /**
     *  Check whether category identifier is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericPageIdentifier(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     * Assign category to store views
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $oldStoreIds = $this->lookupStoreIds($object->getId());
        $newStoreIds = (array)$object->getStoreIds();
        if (!$newStoreIds) {
            $newStoreIds = [0];
        }

        $table = $this->getTable('omnyfy_cms_category_store');
        $insert = array_diff($newStoreIds, $oldStoreIds);
        $delete = array_diff($oldStoreIds, $newStoreIds);

        if ($delete) {
            $where = ['category_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['category_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        $links = $object->getData('links');

        //\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug('$links ' . print_r($links, true));
        $linkType = 'article';
        $linkField = 'article_id';
        $tableName = 'omnyfy_cms_article_category';

        if (!empty($links[$linkType]) && is_array($links[$linkType])) {
            $linksData = $links[$linkType];
            $linksDataKeys = array_keys($linksData);
        } else {
            $linksData = [];
            $linksDataKeys = [];
        }
        $oldIds = $this->lookupAssignedArticleIds($object->getId());
        //\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug('$linksDataKeys ' . print_r($linksDataKeys, true));
        //\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug('$oldIds ' . print_r($oldIds, true));
        //\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug('$linksData ' . print_r($linksData, true));

        $this->_updateLinks(
            $object, $linksDataKeys, $oldIds, $tableName, $linkField, $linksData
        );
        //$this->updateArticlePosition($linksData);

        return parent::_afterSave($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $categoryId
     * @return array
     */
    public function lookupStoreIds($categoryId)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
            $this->getTable('omnyfy_cms_category_store'), 'store_id'
        )->where(
            'category_id = ?', (int)$categoryId
        );

        return $adapter->fetchCol($select);
    }

    /**
     * Get assigned article ids to which specified category is assigned
     *
     * @param int $categoryId
     * @return array
     */
    public function lookupAssignedArticleIds($categoryId)
    {
        return $this->_lookupIds($categoryId, 'omnyfy_cms_article_category', 'article_id');
    }

    /**
     * Get ids to which specified item is assigned
     * @param int $categoryId
     * @param string $tableName
     * @param string $field
     * @return array
     */
    protected function _lookupIds($categoryId, $tableName, $field)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
            $this->getTable($tableName), $field
        )->where(
            'category_id = ?', (int)$categoryId
        );
        //echo $select; die('category lookup');
        //echo $select->getSelect(); die('category lookup');
        return $adapter->fetchCol($select);
    }

    /**
     * Update category connections
     * @param AbstractModel $object
     * @param Array $newRelatedIds
     * @param Array $oldRelatedIds
     * @param String $tableName
     * @param String $field
     * @param Array $rowData
     * @return void
     */
    protected function _updateLinks(
        AbstractModel $object, array $newRelatedIds, array $oldRelatedIds, $tableName, $field, $rowData = []
    )
    {
        $table = $this->getTable($tableName);

        $insert = $newRelatedIds;
        $delete = $oldRelatedIds;

        if (!empty($delete)) {
            $where = ['category_id = ?' => (int)$object->getId(), $field . ' IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if (!empty($insert)) {
            $data = [];

            foreach ($insert as $id) {
                $id = (int)$id;
                $data[] = array_merge(['category_id' => (int)$object->getId(), $field => $id], (isset($rowData[$id]) && is_array($rowData[$id])) ? $rowData[$id] : []
                );
            }
            //\Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->debug('update $data ' . print_r($data, true));
            $this->getConnection()->insertMultiple($table, $data);
        }
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $storeIds = $this->lookupStoreIds($object->getId());
            $object->setData('store_ids', $storeIds);
        }

        return parent::_afterLoad($object);
    }

}
