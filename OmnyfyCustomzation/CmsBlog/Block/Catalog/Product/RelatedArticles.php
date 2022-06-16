<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Catalog\Product;

use Magento\Catalog\Model\Product;
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
            'mfcms/product_page/related_articles_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Block Identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [Product::CACHE_TAG . '_relatedarticles_' . $this->getArticle()->getId()];
    }

    /**
     * Prepare articles collection
     *
     * @return void
     */
    protected function _prepareArticleCollection()
    {
        $pageSize = (int)$this->_scopeConfig->getValue(
            'mfcms/product_page/number_of_related_articles',
            ScopeInterface::SCOPE_STORE
        );
        if (!$pageSize) {
            $pageSize = 5;
        }
        $this->setPageSize($pageSize);

        parent::_prepareArticleCollection();

        $product = $this->getProduct();
        $this->_articleCollection->getSelect()->joinLeft(
            ['rl' => $product->getResource()->getTable('omnyfy_cms_article_relatedproduct')],
            'main_table.article_id = rl.article_id',
            ['position']
        )->where(
            'rl.related_id = ?',
            $product->getId()
        );
    }

    /**
     * Retrieve articles instance
     *
     * @return Category
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product',
                $this->_coreRegistry->registry('current_product')
            );
        }
        return $this->getData('product');
    }
}
