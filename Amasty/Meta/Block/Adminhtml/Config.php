<?php

namespace Amasty\Meta\Block\Adminhtml;

class Config extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $isCustom = $this->getIsCustom();
        $title = $this->getTitle();
        $this->_controller      = isset($isCustom) && $isCustom === true
            ? 'adminhtml_custom' : 'adminhtml_config';
        $this->_blockGroup      = 'Amasty\Meta';

        $this->_headerText      = __($title);
        $this->_addButtonConfig = __('Add Template');
        
        parent::_construct();
    }
}
