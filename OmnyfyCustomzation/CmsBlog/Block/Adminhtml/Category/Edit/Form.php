<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Admin cms category edit form form
 */
class Form extends Generic
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'article']]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
