<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article;

use DOMDocument;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use OmnyfyCustomzation\CmsBlog\Block\Article\Info;
use OmnyfyCustomzation\CmsBlog\Model\Article;
use OmnyfyCustomzation\CmsBlog\Model\ArticleFactory;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Abstract article мшуц block
 */
abstract class AbstractArticle extends Template
{

    /**
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var Article
     */
    protected $_article;

    /**
     * Page factory
     *
     * @var ArticleFactory
     */
    protected $_articleFactory;

    /**
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var string
     */
    protected $_defaultArticleInfoBlock = 'OmnyfyCustomzation\CmsBlog\Block\Article\Info';

    /**
     * @var Url
     */
    protected $_url;

    /**
     * Construct
     *
     * @param Context $context
     * @param Article $article
     * @param Registry $coreRegistry ,
     * @param FilterProvider $filterProvider
     * @param ArticleFactory $articleFactory
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        Article $article,
        Registry $coreRegistry,
        FilterProvider $filterProvider,
        ArticleFactory $articleFactory,
        Url $url,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_article = $article;
        $this->_coreRegistry = $coreRegistry;
        $this->_filterProvider = $filterProvider;
        $this->_articleFactory = $articleFactory;
        $this->_url = $url;
    }

    /**
     * Retrieve article short content
     *
     * @return string
     */
    public function getShorContent()
    {
        $content = $this->getContent();
        $pageBraker = '<!-- pagebreak -->';

        if ($p = mb_strpos($content, $pageBraker)) {
            $content = mb_substr($content, 0, $p);
        }

        $dom = new DOMDocument();
        $dom->loadHTML($content);
        $content = $dom->saveHTML();

        return $content;
    }

    /**
     * Retrieve article content
     *
     * @return string
     */
    public function getContent()
    {
        $article = $this->getArticle();
        $key = 'filtered_content';
        if (!$article->hasData($key)) {
            $cotent = $this->_filterProvider->getPageFilter()->filter(
                $article->getContent()
            );
            $article->setData($key, $cotent);
        }
        return $article->getData($key);
    }

    /**
     * Retrieve article instance
     *
     * @return Article
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

    /**
     * Retrieve article info html
     *
     * @return string
     */
    public function getInfoHtml()
    {
        return $this->getInfoBlock()->toHtml();
    }

    /**
     * Retrieve article info block
     *
     * @return Info
     */
    public function getInfoBlock()
    {
        $k = 'info_block';
        if (!$this->hasData($k)) {
            $blockName = $this->getArticleInfoBlockName();
            if ($blockName) {
                $block = $this->getLayout()->getBlock($blockName);
            }

            if (empty($block)) {
                $block = $this->getLayout()->createBlock($this->_defaultArticleInfoBlock, uniqid(microtime()));
            }

            $this->setData($k, $block);
        }

        return $this->getData($k)->setArticle($this->getArticle());
    }

}
