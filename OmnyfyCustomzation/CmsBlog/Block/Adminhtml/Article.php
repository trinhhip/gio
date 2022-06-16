<?php

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Admin cms article
 */
class Article extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_article';
        $this->_blockGroup = 'OmnyfyCustomzation_CmsBlog';
        $this->_headerText = __('Article');
        $this->_addButtonLabel = __('Add New Article');
        parent::_construct();
    }
}
