<?php

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel\ToolTemplate;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Cms userType collection
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
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\ToolTemplate', 'OmnyfyCustomzation\CmsBlog\Model\ResourceModel\ToolTemplate');
    }

}
