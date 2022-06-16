<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml\Article\Edit\Tab;

use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\Collection;

/**
 * Admin cms article edit form main tab
 */
class Main extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Collection
     */
    protected $_categoryCollection;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param Collection $categoryCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Collection $categoryCollection,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_categoryCollection = $categoryCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Article Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Article Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');
        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('OmnyfyCustomzation_CmsBlog::article');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('article_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Article Information')]);

        if ($model->getId()) {
            $fieldset->addField('article_id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'article[title]',
                'label' => __('Article Title'),
                'title' => __('Article Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'identifier',
            'text',
            [
                'name' => 'article[identifier]',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
                'class' => 'validate-identifier',
                'note' => __('Relative to Web Site Base URL'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Article Status'),
                'name' => 'article[is_active]',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'article[stores][]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled,
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'article[stores][]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $categories[] = ['label' => __('Please select'), 'value' => 0];
        $collection = $this->_categoryCollection
            ->setOrder('position')
            ->getTreeOrderedArray();

        foreach ($collection as $item) {
            $categories[] = array(
                'label' => $this->_getSpaces($item->getLevel()) . ' ' . $item->getTitle() . ($item->getIsActive() ? '' : ' (' . __('Disabled') . ')'),
                'value' => $item->getId(),
            );
        }

        $field = $fieldset->addField(
            'categories',
            'multiselect',
            [
                'name' => 'article[categories][]',
                'label' => __('Categories'),
                'title' => __('Categories'),
                'values' => $categories,
                'disabled' => $isElementDisabled,
                'style' => 'width:100%',
            ]
        );

        if (is_array($model->getData('featured_img'))) {
            $model->setData('featured_img', $model->getData('featured_img')['value']);
        }
        $fieldset->addField(
            'featured_img',
            'image',
            [
                'title' => __('Featured Image'),
                'label' => __('Featured Image'),
                'name' => 'article[featured_img]',
                'note' => __('Allow image type: jpg, jpeg, gif, png'),
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            IntlDateFormatter::SHORT
        );

        $fieldset->addField(
            'publish_time',
            'date',
            [
                'name' => 'article[publish_time]',
                'label' => __('Publish At'),
                'date_format' => $dateFormat,
                'disabled' => $isElementDisabled,
                'class' => 'validate-date validate-date-range date-range-custom_theme-from'
            ]
        );


        $this->_eventManager->dispatch('omnyfy_cms_article_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Generate spaces
     * @param int $n
     * @return string
     */
    protected function _getSpaces($n)
    {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= '--- ';
        }

        return $s;
    }
}
