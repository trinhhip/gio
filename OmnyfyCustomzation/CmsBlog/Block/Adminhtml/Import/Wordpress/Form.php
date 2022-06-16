<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml\Import\Wordpress;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;

/**
 * Wordpress import form block
 */
class Form extends Generic
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        //Magento\Backend\Model\Session
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'article']]
        );
        $form->setUseContainer(true);

        $data = $this->_coreRegistry->registry('import_config')->getData();

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_authorization->isAllowed('OmnyfyCustomzation_CmsBlog::import')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $isElementDisabled = false;


        $form->setHtmlIdPrefix('import_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => '']);

        $fieldset->addField(
            'type',
            'hidden',
            [
                'name' => 'type',
                'required' => true,
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'notice',
            'label',
            [
                'label' => __('NOTICE'),
                'name' => 'prefix',
                'after_element_html' => 'When the import is completed successfully, please copy image files from WordPress <strong style="color:#bd1616;">wp-content/uploads</strong> directory to Magento <strong style="color:#105610;">pub/media/omnyfyCustomzation_cmsblog</strong> directory.',
            ]
        );

        $fieldset->addField(
            'dbname',
            'text',
            [
                'name' => 'dbname',
                'label' => __('Database Name'),
                'title' => __('Database Name'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>The name of the database you run in WP.</small>',
            ]
        );

        $fieldset->addField(
            'uname',
            'text',
            [
                'label' => __('User Name'),
                'title' => __('User Name'),
                'name' => 'uname',
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>Your WP MySQL username.</small>',
            ]
        );

        $fieldset->addField(
            'pwd',
            'text',
            [
                'label' => __('Password'),
                'title' => __('Password'),
                'name' => 'pwd',
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>…and your WP MySQL password.</small>',
            ]
        );

        $fieldset->addField(
            'dbhost',
            'text',
            [
                'label' => __('Database Host'),
                'title' => __('Database Host'),
                'name' => 'dbhost',
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>…and your WP MySQL host.</small>',
            ]
        );

        $fieldset->addField(
            'prefix',
            'text',
            [
                'label' => __('Table Prefix'),
                'title' => __('Table Prefix'),
                'name' => 'prefix',
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>…and your WP MySQL table prefix.</small>',
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store_id',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, false),
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
                ['name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId()]
            );

            $data['store_id'] = $this->_storeManager->getStore(true)->getId();
        }

        $this->_eventManager->dispatch('omnyfyCustomzation_cmsblog_import_wordpress_prepare_form', ['form' => $form]);

        /*if (empty($data['homepageurl'])) {
            $data['homepageurl'] = $this->getUrl('cms', ['_store' => 1]);
        }*/

        if (empty($data['prefix'])) {
            $data['prefix'] = 'wp_';
        }

        if (empty($data['dbhost'])) {
            $data['dbhost'] = 'localhost';
        }

        $data['type'] = 'wordpress';

        $form->setValues($data);

        $this->setForm($form);


        return parent::_prepareForm();
    }
}