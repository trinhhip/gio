<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList;

use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Block\Html\Pager;

/**
 * Cms articles list toolbar
 */
class Toolbar extends Template
{
    /**
     * Page GET parameter name
     */
    const PAGE_PARM_NAME = 'page';

    /**
     * Products collection
     *
     * @var AbstractCollection
     */
    protected $_collection = null;

    /**
     * Default block template
     * @var string
     */
    protected $_template = 'article/list/toolbar.phtml';

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('article_list_toolbar_pager');
        if ($pagerBlock instanceof DataObject) {
            /* @var $pagerBlock Pager */

            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setPageVarName(
                'page'
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimit()
            )->setCollection(
                $this->getCollection()
            );
            return $pagerBlock->toHtml();
        }

        return '';
    }

    /**
     * Get specified articles limit display per page
     *
     * @return string
     */
    public function getLimit()
    {
        return $this->_scopeConfig->getValue(
            'mfcms/article_list/articles_per_page',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return products collection instance
     *
     * @return AbstractCollection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Set collection to pager
     *
     * @param Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
        return $this;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = (int)$this->_request->getParam(self::PAGE_PARM_NAME);
        return $page ? $page : 1;
    }
}
