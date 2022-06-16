<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Widget;

use DOMDocument;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Block\BlockInterface;
use OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList\AbstractList;
use OmnyfyCustomzation\CmsBlog\Model\Article;
use OmnyfyCustomzation\CmsBlog\Model\Category;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Cms recent articles widget
 */
class Recent extends AbstractList implements BlockInterface
{

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var Category
     */
    protected $_category;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param Registry $coreRegistry
     * @param FilterProvider $filterProvider
     * @param CollectionFactory $articleCollectionFactory
     * @param Url $url
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FilterProvider $filterProvider,
        CollectionFactory $articleCollectionFactory,
        Url $url,
        CategoryFactory $categoryFactory,
        array $data = []
    )
    {
        parent::__construct($context, $coreRegistry, $filterProvider, $articleCollectionFactory, $url, $data);
        $this->_categoryFactory = $categoryFactory;
    }

    /**
     * Set cms template
     *
     * @return this
     */
    public function _toHtml()
    {
        $this->setTemplate(
            $this->getData('custom_template') ?: 'widget/recent.phtml'
        );

        return parent::_toHtml();
    }

    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title') ?: __('Recent Cms Articles');
    }

    /**
     * Retrieve article short content
     * @param Article $article
     *
     * @return string
     */
    public function getShorContent($article)
    {
        $content = $article->getContent();
        $pageBraker = '<!-- pagebreak -->';

        if ($p = mb_strpos($content, $pageBraker)) {
            $content = mb_substr($content, 0, $p);
        }

        $content = $this->_filterProvider->getPageFilter()->filter($content);

        $dom = new DOMDocument();
        $dom->loadHTML($content);
        $content = $dom->saveHTML();

        return $content;
    }

    /**
     * Prepare articles collection
     *
     * @return void
     */
    protected function _prepareArticleCollection()
    {
        $size = $this->getData('number_of_articles');
        if (!$size) {
            $size = (int)$this->_scopeConfig->getValue(
                'mfcms/sidebar/recent_articles/articles_per_page',
                ScopeInterface::SCOPE_STORE
            );
        }

        $this->setPageSize($size);

        parent::_prepareArticleCollection();

        if ($category = $this->getCategory()) {
            $categories = $category->getChildrenIds();
            $categories[] = $category->getId();
            $this->_articleCollection->addCategoryFilter($categories);
        }
    }

    /**
     * Retrieve category instance
     *
     * @return Category
     */
    public function getCategory()
    {
        if ($this->_category === null) {
            if ($categoryId = $this->getData('category_id')) {
                $category = $this->_categoryFactory->create();
                $category->load($categoryId);

                $storeId = $this->_storeManager->getStore()->getId();
                if ($category->isVisibleOnStore($storeId)) {
                    $category->setStoreId($storeId);
                    return $this->_category = $category;
                }
            }

            $this->_category = false;
        }

        return $this->_category;
    }
}
