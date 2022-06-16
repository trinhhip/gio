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

/**
 * Cms tag resource model
 */
class Tag extends AbstractDb
{

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
        $this->_init('omnyfyCustomzation_cmsblog_tag', 'tag_id');
    }

    /**
     * Process tag data before deleting
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['tag_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('omnyfy_cms_article_tag'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process tag data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setTitle(
            trim(strtolower($object->getTitle()))
        );

        if (!$object->getId()) {
            $tag = $object->getCollection()
                ->addFieldToFilter('title', $object->getTitle())
                ->setPageSize(1)
                ->getFirstItem();
            if ($tag->getId()) {
                throw new LocalizedException(
                    __('The tag is already exist.')
                );
            }
        }

        $identifierGenerator = ObjectManager::getInstance()
            ->create('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\PageIdentifierGenerator');
        $identifierGenerator->generate($object);

        if (!$this->isValidPageIdentifier($object)) {
            throw new LocalizedException(
                __('The tag URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new LocalizedException(
                __('The tag URL key cannot be made of only numbers.')
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

}
