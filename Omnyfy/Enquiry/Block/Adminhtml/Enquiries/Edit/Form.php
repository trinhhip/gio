<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/8/2018
 * Time: 2:43 PM
 */

namespace Omnyfy\Enquiry\Block\Adminhtml\Enquiries\Edit;


use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'    => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}