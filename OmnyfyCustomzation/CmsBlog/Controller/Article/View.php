<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Article;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\CmsBlog\Model\Article;

/**
 * Cms article view
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
     * View Cms article action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $article = $this->_initArticle();
        if (!$article) {
            $this->_forward('index', 'noroute', 'cms');
            return;
        }

        $this->_objectManager->get('\Magento\Framework\Registry')
            ->register('current_cms_article', $article);
        $resultPage = $this->_objectManager->get('OmnyfyCustomzation\CmsBlog\Helper\Page')
            ->prepareResultPage($this, $article);
        return $resultPage;
    }

    /**
     * Init Article
     *
     * @return Article || false
     */
    protected function _initArticle()
    {
        $id = $this->getRequest()->getParam('id');
        $storeId = $this->_storeManager->getStore()->getId();

        $article = $this->_objectManager->create('OmnyfyCustomzation\CmsBlog\Model\Article')->load($id);

        $article->setArticleCounter($article->getArticleCounter() + 1);
        $article->setArticleCounterUpdate(1);
        $article->save();

        if (!$article->isVisibleOnStore($storeId)) {
            return false;
        }

        $article->setStoreId($storeId);

        return $article;
    }

}
