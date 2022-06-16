<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Search;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;

/**
 * Cms search results view
 */
class Index extends Action
{
    /**
     * View cms search results action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
