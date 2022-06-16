<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\CmsBlog\Model\category;

/**
 * Cms category view
 */
class View extends Action
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    /**
     * View cms category action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $category = $this->_initCategory();
        if (!$category) {
            $this->_forward('index', 'noroute', 'cms');
            return;
        }

        $this->_objectManager->get('\Magento\Framework\Registry')
            ->register('current_cms_category', $category);

        $resultPage = $this->_objectManager->get('OmnyfyCustomzation\CmsBlog\Helper\Page')
            ->prepareResultPage($this, $category);
        return $resultPage;
    }

    /**
     * Init category
     *
     * @return category || false
     */
    protected function _initCategory()
    {
        $id = $this->getRequest()->getParam('id');
        $storeId = $this->_storeManager->getStore()->getId();

        $category = $this->_objectManager->create('OmnyfyCustomzation\CmsBlog\Model\Category')->load($id);

        if (!$category->isVisibleOnStore($storeId)) {
            return false;
        }

        $category->setStoreId($storeId);

        return $category;
    }
}
