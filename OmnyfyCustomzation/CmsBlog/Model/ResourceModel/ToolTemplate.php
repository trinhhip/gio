<?php

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Cms userType resource model
 */
class ToolTemplate extends AbstractDb
{

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfyCustomzation_cmsblog_tool_template', 'id');
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
        $this->getConnection()->delete($this->getTable('omnyfyCustomzation_cmsblog_tool_template'), $condition);

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
        /* $object->setTitle(
            trim(strtolower($object->getTitle()))
        ); */

        $title = $object->getTitle();

        if (!$object->getId()) {
            $userType = $object->getCollection()
                ->addFieldToFilter('title', $title)
                ->setPageSize(1)
                ->getFirstItem();
            if ($userType->getId()) {
                throw new LocalizedException(
                    __('The tool template is already exist.')
                );
            }
        }

        return parent::_beforeSave($object);
    }
}
