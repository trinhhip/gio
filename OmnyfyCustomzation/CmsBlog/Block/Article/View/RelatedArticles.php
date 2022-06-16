<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article\View;

use Magento\Cms\Model\Page;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList\AbstractList;
use OmnyfyCustomzation\CmsBlog\Model\Category;

/**
 * Cms article related articles block
 */
class RelatedArticles extends AbstractList
{
    /**
     * Retrieve true if Display Related Articles enabled
     * @return boolean
     */
    public function displayArticles()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfcms/article_view/related_articles/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Block Identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [Page::CACHE_TAG . '_relatedarticles_' . $this->getArticle()->getId()];
    }

    /**
     * Prepare articles collection
     *
     * @return void
     */
    protected function _prepareArticleCollection()
    {
        $pageSize = (int)$this->_scopeConfig->getValue(
            'mfcms/article_view/related_articles/number_of_articles',
            ScopeInterface::SCOPE_STORE
        );

        $this->_articleCollection = $this->getArticle()->getRelatedArticles()
            ->addActiveFilter()
            ->setPageSize($pageSize ?: 5);

        $this->_articleCollection->getSelect()->order('rl.position', 'ASC');
    }

    /**
     * Retrieve articles instance
     *
     * @return Category
     */
    public function getArticle()
    {
        if (!$this->hasData('article')) {
            $this->setData('article',
                $this->_coreRegistry->registry('current_cms_article')
            );
        }
        return $this->getData('article');
    }
}
