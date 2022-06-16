<?php

namespace Omnyfy\Enquiry\Block\Adminhtml\Location\Edit\Tab;


class VendorMakeAnEnquiry extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_enquiryHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Array $data = [],
        \Omnyfy\Enquiry\Helper\Data $enquiryHelper
    )
    {
        $this->_enquiryHelper = $enquiryHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Make an Enquiry');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Make an Enquiry');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if ($this->_enquiryHelper->isEnabled($this->getVendorId()))
            return true;
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        if ($this->_enquiryHelper->isEnabled($this->getVendorId()))
            return false;
        return true;
    }

    public function isAjaxLoaded()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_omnyfy_vendor_vendor');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('vendor_');


        $fieldset = $form->addFieldset('make_an_enquiry', ['legend' => __('Make An Enquiry')]);
        $fieldset->addField(
            'enquiry_for_vendor',
            'select',
            [
                'name' => 'enquiry_for_vendor',
                'label' => __('Enable Enquiry for Vendor'),
                'title' => __('Enable Enquiry for Vendor'),
                'values' => $this->toOptionArray(),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'enquiry_for_products',
            'select',
            [
                'name' => 'enquiry_for_products',
                'label' => __('Enable Enquiry for Products'),
                'title' => __('Enable Enquiry for Products'),
                'values' => $this->toOptionArray(),
                'required' => true,
            ]
        );

        $form->setData('enquiry_for_vendor',1);
        $form->setData('enquiry_for_products',1);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Enable')],
            ['value' => '0', 'label' => __('Disable')]
        ];
    }

    protected function getVendorId()
    {
        $vendor = $this->_coreRegistry->registry('current_omnyfy_vendor_vendor');
        return empty($vendor) ? 0 : $vendor->getId();
    }
}