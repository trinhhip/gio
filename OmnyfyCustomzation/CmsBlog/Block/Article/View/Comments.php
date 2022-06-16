<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article\View;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Model\Category;

/**
 * Cms article comments block
 */
class Comments extends Template
{
    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * Block template file
     * @var string
     */
    protected $_template = 'article/view/comments.phtml';

    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $coreRegistry ,
     * @param ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ResolverInterface $localeResolver,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_localeResolver = $localeResolver;
    }

    /**
     * Retrieve comments type
     * @return bool
     */
    public function getCommentsType()
    {
        return $this->_scopeConfig->getValue(
            'mfcms/article_view/comments/type', ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve number of comments to display
     * @return int
     */
    public function getNumberOfComments()
    {
        return (int)$this->_scopeConfig->getValue(
            'mfcms/article_view/comments/number_of_comments', ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve facebook app id
     * @return string
     */
    public function getFacebookAppId()
    {
        return $this->_scopeConfig->getValue(
            'mfcms/article_view/comments/fb_app_id', ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve disqus forum shortname
     * @return string
     */
    public function getDisqusShortname()
    {
        return $this->_scopeConfig->getValue(
            'mfcms/article_view/comments/disqus_forum_shortname', ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve locale code
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->_localeResolver->getLocale();
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
