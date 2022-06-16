<?php

/**
 * Project: Vendor SignUp
 * User: jing
 * Date: 2019-08-02
 * Time: 12:31
 */

namespace Omnyfy\VendorSignUp\Block\SignUp;

use Magento\Framework\View\Element\Template;
use Omnyfy\Vendor\Helper\Attribute;

class Attributes extends \Magento\Framework\View\Element\Template
{
    protected $registry;

    protected $attrHelper;

    protected $vendorAttributes;

    protected $elementFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Omnyfy\Vendor\Helper\Attribute $attrHelper,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    ) {
        $this->registry = $coreRegistry;
        $this->attrHelper = $attrHelper;
        $this->elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    public function getVendorAttributes()
    {
        if (empty($this->vendorAttributes)) {
            $vendorType = $this->registry->registry('current_omnyfy_vendor_type');
            $vendorTypeId = $this->getRequest()->getParam('type_id', null);
            $this->vendorAttributes = $this->attrHelper->getVendorSignUpAttributes($vendorTypeId)->getItems();
        }

        return $this->vendorAttributes;
    }

    public function getAttributeHtml($attribute)
    {
        $inputType = $attribute->getFrontend()->getInputType();
        $isDatetime = false;
        if ($inputType == 'datetime') {
            $inputType = 'date';
            $isDatetime = true;
        }
        if ($inputType == 'boolean') {
            $inputType = 'select';
        }

        $fieldType = $inputType;

        if ($fieldType == 'date' && $isDatetime) {
            $element = $this->elementFactory->create($fieldType, [
                'data' =>
                [
                    'name' => "extend_attribute[" . $attribute->getAttributeCode() . "]",
                    'label' => $attribute->getFrontend()->getLocalizedLabel(),
                    'class' => $attribute->getFrontend()->getClass(),
                    'required' => $attribute->getIsRequired(),
                    'note' => $attribute->getNote(),
                    'is_datetime' => 1
                ]
            ]);
        } elseif ($fieldType == 'date' && !$isDatetime) {
            $element = $this->elementFactory->create($fieldType, [
                'data' =>
                [
                    'name' => "extend_attribute[" . $attribute->getAttributeCode() . "]",
                    'label' => $attribute->getFrontend()->getLocalizedLabel(),
                    'class' => $attribute->getFrontend()->getClass(),
                    'required' => $attribute->getIsRequired(),
                    'note' => $attribute->getNote(),
                    'is_date' => 1
                ]
            ]);
        } elseif ($inputType == 'boolean') {
            $element = $this->elementFactory->create($fieldType, [
                'data' =>
                [
                    'name' => "extend_attribute[" . $attribute->getAttributeCode() . "]",
                    'label' => $attribute->getFrontend()->getLocalizedLabel(),
                    'class' => $attribute->getFrontend()->getClass(),
                    'required' => $attribute->getIsRequired(),
                    'note' => $attribute->getNote(),
                    'is_boolean' => 1
                ]
            ]);
        } elseif ($fieldType == 'document') {
            $fieldType = 'Omnyfy\Vendor\Helper\Vendor\Element\Document';
            $element = $this->elementFactory->create($fieldType, [
                'data' =>
                [
                    'name' => "extend_attribute[" . $attribute->getAttributeCode() . "]",
                    'label' => $attribute->getFrontend()->getLocalizedLabel(),
                    'class' => $attribute->getFrontend()->getClass(),
                    'required' => $attribute->getIsRequired(),
                    'note' => $attribute->getNote()
                ]
            ]);
        } else {
            $element = $this->elementFactory->create($fieldType, [
                'data' =>
                [
                    'name' => "extend_attribute[" . $attribute->getAttributeCode() . "]",
                    'label' => $attribute->getFrontend()->getLocalizedLabel(),
                    'class' => $attribute->getFrontend()->getClass(),
                    'required' => $attribute->getIsRequired(),
                    'note' => $attribute->getNote()
                ]
            ]);
        }

        $element->setId($attribute->getAttributeCode());

        $this->_applyTypeSpecificConfig($inputType, $element, $attribute);
        $element->setForm($this);
        $renderer = $this->getLayout()->createBlock(\Omnyfy\VendorSignUp\Block\SignUp\Renderers\Renderer::class);
        $element->setRenderer($renderer);

        return $element->toHtml();
    }

    protected function _applyTypeSpecificConfig($inputType, $element, \Magento\Eav\Model\Entity\Attribute $attribute)
    {
        switch ($inputType) {
            case 'select':
                $element->setValues($attribute->getSource()->getAllOptions(true, true));
                break;
            case 'multiselect':
                $element->setValues($attribute->getSource()->getAllOptions(false, true));
                $element->setCanBeEmpty(true);
                break;
            case 'date':
                $element->setDateFormat($this->_localeDate->getDateFormatWithLongYear());
                break;
            case 'multiline':
                $element->setLineCount($attribute->getMultilineCount());
                break;
            default:
                break;
        }
    }
}
