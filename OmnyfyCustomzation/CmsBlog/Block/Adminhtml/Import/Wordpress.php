<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml\Import;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Wordpress import block
 */
class Wordpress extends Container
{

    /**
     * Get form save URL
     *
     * @return string
     * @see getFormActionUrl()
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/run', ['_current' => true]);
    }

    /**
     * Initialize wordpress import block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'OmnyfyCustomzation_CmsBlog';
        $this->_controller = 'adminhtml_import';
        $this->_mode = 'wordpress';

        parent::_construct();

        if (!$this->_isAllowedAction('OmnyfyCustomzation_CmsBlog::import')) {
            $this->buttonList->remove('save');
        } else {
            $this->updateButton(
                'save', 'label', __('Start Import')
            );
        }

        $this->buttonList->remove('delete');
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

}
