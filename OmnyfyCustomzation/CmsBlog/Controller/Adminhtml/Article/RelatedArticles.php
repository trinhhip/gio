<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Article;

use Magento\Framework\Controller\ResultInterface;
use OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Article;

/**
 * Cms article related articles controller
 */
class RelatedArticles extends Article
{
    /**
     * View related articles action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $model = $this->_getModel();
        $this->_getRegistry()->register('current_model', $model);

        $this->_view->loadLayout()
            ->getLayout()
            ->getBlock('cms.article.edit.tab.relatedarticles')
            ->setArticlesRelated($this->getRequest()->getArticle('articles_related', null));

        $this->_view->renderLayout();
    }
}
