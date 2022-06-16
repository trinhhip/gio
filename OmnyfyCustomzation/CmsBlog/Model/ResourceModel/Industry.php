<?php
/**
 * Project: CMS Industry M2.
 * User: abhay
 * Date: 01/05/17
 * Time: 2:30 PM
 */

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use const Magento\Framework\Model\AbstractModel;

/**
 * Cms Industry resource model
 */
class Industry extends AbstractDb
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param DateTime $dateTime
     * @param string|null $resourcePrefix
     */
    public function __construct(
        Context $context, \Magento\Framework\Stdlib\DateTime\DateTime $date, DateTime $dateTime, $resourcePrefix = null
    )
    {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
        $this->dateTime = $dateTime;
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
     *  Check whether industry identifier is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    public function isNumericPageIdentifier(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether industry identifier is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    public function isValidPageIdentifier(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
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
//        if (!is_array($storeIds)) {
//            $storeIds = [$storeIds];
//        }
//        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
//        $select = $this->_getLoadByIdentifierSelect($identifier, $storeIds, 1);
//        $select->reset(\Zend_Db_Select::COLUMNS)->columns('cp.category_id')->order('cps.store_id DESC')->limit(1);
//
//        return $this->getConnection()->fetchOne($select);
        $object = AbstractModel;
        return $object->getId();
    }

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfyCustomzation_cmsblog_industry', 'id');
    }

    /**
     * Process Industry data before deleting
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('omnyfyCustomzation_cmsblog_industry'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process Industry data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $gmtDate = $this->_date->gmtDate();

        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($gmtDate);
        }

        $object->setModifiedAt($gmtDate);

        return parent::_beforeSave($object);
    }

}
