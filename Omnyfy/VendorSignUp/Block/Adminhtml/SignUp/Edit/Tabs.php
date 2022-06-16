<?php

namespace Omnyfy\VendorSignUp\Block\Adminhtml\SignUp\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('omnyfy_vendorsignup.edit.tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Sign Up'));
    }

    protected function _prepareLayout()
    {
        $this->addTab('main_section',
            [
                'label' => __('Info'),
                'title' => __('Info'),
                'block' => 'omnyfy_vendorsignup.edit.tab.main'
            ]
        );
        return parent::_prepareLayout();
    }
}
