<?php

namespace Amasty\Meta\Block\Adminhtml\Custom\Edit;

use Amasty\Meta\Api\Data\ConfigInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        '*/*/save',
                        ['id' => $this->getRequest()->getParam('id')]
                    ),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );
        
        $form->setUseContainer(true);
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
}
