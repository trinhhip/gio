<?php

namespace Omnyfy\Vendor\Block\Adminhtml\Location\Edit\Tab;

class ContactInfo extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_wysiwygConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Contact Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Contact Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_omnyfy_vendor_location');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('location_');
        $fieldset = $form->addFieldset('contact_info_fieldset', ['legend' => __('Contact Information')]);
        $fieldset->addField(
            'location_contact_name',
            'text',
            ['label' => __('Location Contact Name'),  'data-form-part' => 'omnyfy_vendor_location_form', 'required' => true, 'name' => 'location[location_contact_name]', 'class' => 'required-entry',]
        );
        $fieldset->addField(
            'location_contact_phone',
            'text',
            ['label' => __('Location Contact Phone'),  'data-form-part' => 'omnyfy_vendor_location_form', 'required' => true, 'name' => 'location[location_contact_phone]', 'class' => 'required-entry',]
        );
        $fieldset->addField(
            'location_contact_email',
            'text',
            ['label' => __('Location Contact Email'),  'data-form-part' => 'omnyfy_vendor_location_form','required' => true, 'name' => 'location[location_contact_email]',  'class' => 'required-entry validate-email',]
        );
        $fieldset->addField(
            'location_company_name',
            'text',
            ['label' => __('Location Company Name'), 'data-form-part' => 'omnyfy_vendor_location_form', 'required' => true, 'name' => 'location[location_company_name]', 'class' => 'required-entry']
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function isAjaxLoaded()
    {
        return false;
    }
}
