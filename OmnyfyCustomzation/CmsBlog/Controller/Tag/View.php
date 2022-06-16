<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Tag;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultInterface;
use OmnyfyCustomzation\CmsBlog\Model\Tag;

/**
 * Cms tag articles view
 */
class View extends Action
{
    /**
     * View cms author action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $config = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

        $tag = $this->_initTag();
        if (!$tag) {
            $this->_forward('index', 'noroute', 'cms');
            return;
        }

        $this->_objectManager->get('\Magento\Framework\Registry')->register('current_cms_tag', $tag);

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * Init author
     *
     * @return Tag || false
     */
    protected function _initTag()
    {
        $id = $this->getRequest()->getParam('id');

        $tag = $this->_objectManager->create('OmnyfyCustomzation\CmsBlog\Model\Tag')->load($id);

        if (!$tag->getId()) {
            return false;
        }

        return $tag;
    }

}
