<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;

/**
 * Cms prepare wordpress import controller
 */
class Wordpress extends Action
{
    /**
     * Prepare wordpress import
     * @return ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('OmnyfyCustomzation_CmsBlog::import');
        $title = __('Cms Import from WordPress (beta)');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);

        $config = new DataObject(
            (array)$this->_getSession()->getData('import_wordpress_form_data', true) ?: []
        );

        $this->_objectManager->get('\Magento\Framework\Registry')->register('import_config', $config);

        $this->_view->renderLayout();
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('OmnyfyCustomzation_CmsBlog::import');
    }
}
