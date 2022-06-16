<?php

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Admin cms category
 */
class Category extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'OmnyfyCustomzation_CmsBlog';
        $this->_headerText = __('Category');
        $this->_addButtonLabel = __('Add New Category');
        parent::_construct();
    }
}
