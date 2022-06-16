<?php

namespace OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country;

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
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\Country', 'OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country');
    }

}
