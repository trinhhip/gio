<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 2019-05-03
 * Time: 13:49
 */
namespace Omnyfy\Vendor\Block\Adminhtml\Location\Attribute\Edit\Tab;

class Main extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        /** @var \Omnyfy\Vendor\Model\Resource\Eav\Attribute $attributeObject */
        $attributeObject = $this->getAttributeObject();
        /* @var $form \Magento\Framework\Data\Form */
        $form = $this->getForm();
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        $fiedsToRemove = ['attribute_code', 'is_unique', 'frontend_class'];

        foreach ($fieldset->getElements() as $element) {
            /** @var \Magento\Framework\Data\Form\AbstractForm $element  */
            if (substr($element->getId(), 0, strlen('default_value')) == 'default_value') {
                $fiedsToRemove[] = $element->getId();
            }
        }
        foreach ($fiedsToRemove as $id) {
            $fieldset->removeField($id);
        }

        $frontendInputElm = $form->getElement('frontend_input');
        $additionalTypes = [
//            ['value' => 'price', 'label' => __('Price')],
//            ['value' => 'media_image', 'label' => __('Media Image')],
        ];
        $additionalReadOnlyTypes = ['gallery' => __('Gallery')];
        if (isset($additionalReadOnlyTypes[$attributeObject->getFrontendInput()])) {
            $additionalTypes[] = [
                'value' => $attributeObject->getFrontendInput(),
                'label' => $additionalReadOnlyTypes[$attributeObject->getFrontendInput()],
            ];
        }

        $response = new \Magento\Framework\DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_location_attribute_types', ['response' => $response]);
        $_hiddenFields = [];
        foreach ($response->getTypes() as $type) {
            $additionalTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
        }
        $this->_coreRegistry->register('attribute_type_hidden_fields', $_hiddenFields);

        $frontendInputValues = array_merge($frontendInputElm->getValues(), $additionalTypes);
        $frontendInputElm->setValues($frontendInputValues);

        $this->_eventManager->dispatch('vendor_attribute_form_build_main_tab', ['form' => $form]);

        return $this;
    }

    /**
     * Retrieve additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return ['apply' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply'];
    }
}