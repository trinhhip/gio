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

/**
 * Cms aw import controller
 */
class Aw extends Action
{
    /**
     * Prepare aw import
     * @return ResultInterface
     */
    public function execute()
    {
        $this->_redirect('*/*/');
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
