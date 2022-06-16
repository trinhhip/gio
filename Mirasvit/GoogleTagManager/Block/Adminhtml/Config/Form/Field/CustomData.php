<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\GoogleTagManager\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Mirasvit\GoogleTagManager\Block\Adminhtml\Config\Form\Field\CustomDataRenderer\AttributeOption;
use Mirasvit\GoogleTagManager\Block\Adminhtml\Config\Form\Field\CustomDataRenderer\TypeOption;

class CustomData extends AbstractFieldArray
{
    private $attributeRenderer;

    private $typeRenderer;

    protected function _prepareToRender()
    {
        $this->_addAfter = false;

        $this->addColumn('custom_data_type', ['label' => __('Type'), 'renderer' => $this->getTypeRenderer()]);
        $this->addColumn('custom_data_code', ['label' => __('Code')]);
        $this->addColumn('custom_data_attr', ['label' => __('Attribute'), 'renderer' => $this->getAttributeRenderer()]);
        $this->addColumn('custom_data_index', ['label' => __('Index (1 to 50)')]);

        //        $this->_addButtonLabel = __('Add Minimum Qty');
    }

//    public function getArrayRows(DataObject $row) {}
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        if ($row->getData('custom_data_type')) {
            $options['option_' . $this->getTypeRenderer()->calcOptionHash($row->getData('custom_data_type'))]
                = 'selected="selected"';
        }

        if ($row->getData('custom_data_attr')) {
            $options['option_' . $this->getAttributeRenderer()->calcOptionHash($row->getData('custom_data_attr'))]
                = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getTypeRenderer(): TypeOption
    {
        if (!$this->typeRenderer) {
            $this->typeRenderer = $this->getLayout()->createBlock(
                \Mirasvit\GoogleTagManager\Block\Adminhtml\Config\Form\Field\CustomDataRenderer\TypeOption::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->typeRenderer->setClass('admin__control-select');
        }

        return $this->typeRenderer;
    }

    private function getAttributeRenderer(): AttributeOption
    {
        if (!$this->attributeRenderer) {
            $this->attributeRenderer = $this->getLayout()->createBlock(
                \Mirasvit\GoogleTagManager\Block\Adminhtml\Config\Form\Field\CustomDataRenderer\AttributeOption::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->attributeRenderer->setClass('admin__control-select');
        }

        return $this->attributeRenderer;
    }
}
