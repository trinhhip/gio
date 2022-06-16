<?php
/**
 * Project: CMS Industry M2.
 * User: abhay
 * Date: 01/05/17
 * Time: 2:30 PM
 */

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Industry;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Cms Country collection
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\Industry', 'OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Industry');
    }

}
