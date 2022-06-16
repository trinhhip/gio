<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Rss;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;

/**
 * Cms rss feed view
 */
class Feed extends Action
{
    /**
     * View cms rss feed action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->getResponse()
            ->setHeader('Content-type', 'text/xml; charset=UTF-8')
            ->setBody(
                $this->_view->getLayout()->getBlock('cms.rss.feed')->toHtml()
            );
    }

}
