<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article;

use OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList\AbstractList;
use OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList\Toolbar;
use OmnyfyCustomzation\CmsBlog\Model\Article;

/**
 * Cms article list block
 */
class ArticleList extends AbstractList
{
    /**
     * Block template file
     * @var string
     */
    protected $_defaultToolbarBlock = 'OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList\Toolbar';

    /**
     * Retrieve article html
     * @param Article $article
     * @return string
     */
    public function getArticleHtml($article)
    {
        return $this->getChildBlock('cms.articles.list.item')->setArticle($article)->toHtml();
    }

    /**
     * Retrieve Toolbar Html
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $page = $this->_request->getParam(
            Toolbar::PAGE_PARM_NAME
        );

        if ($page > 1) {
            $this->pageConfig->setRobots('NOINDEX,FOLLOW');
        }

        return parent::_prepareLayout();
    }

    /**
     * Before block to html
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->getArticleCollection();

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
        $this->setData('limit', $toolbar->getLimit());

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar Block
     * @return Toolbar
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();

        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
    }

}
