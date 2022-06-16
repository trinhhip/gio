<?php

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;

/**
 * Cms userType resource model
 */
class UserType extends AbstractDb
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
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfyCustomzation_cmsblog_user_type', 'id');
    }

    /**
     * Process userType data before deleting
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('omnyfyCustomzation_cmsblog_user_type'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process userType data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $userType = trim(strtolower($object->getUserType()));

        if (!$object->getId()) {
            $userType = $object->getCollection()
                ->addFieldToFilter('user_type', $userType)
                ->setPageSize(1)
                ->getFirstItem();
            if ($userType->getId()) {
                throw new LocalizedException(
                    __('The user type is already exist.')
                );
            }
        }

        $gmtDate = $this->_date->gmtDate();

        if ($object->isObjectNew() && !$object->getCreatedAt()) {
            $object->setCreatedAt($gmtDate);
        }

        $object->setModifiedAt($gmtDate);

        return parent::_beforeSave($object);
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
     *  Check whether category identifier is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

}
