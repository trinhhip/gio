<?php

namespace OmnyfyCustomzation\CmsBlog\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList;

/**
 * Cms index block
 */
class Index extends ArticleList
{
    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $this->pageConfig->getTitle()->set($this->_getConfigValue('title'));
        $this->pageConfig->setKeywords($this->_getConfigValue('meta_keywords'));
        $this->pageConfig->setDescription($this->_getConfigValue('meta_description'));
        $this->pageConfig->addRemotePageAsset(
            $this->_url->getBaseUrl(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _addBreadcrumbs()
    {
        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'cms',
                [
                    'label' => $this->_scopeConfig->getValue('mfcms/index_page/title', ScopeInterface::SCOPE_STORE),
                    'title' => __(sprintf('Go to Cms Home Page'))
                ]
            );
        }
    }

    /**
     * Retrieve cms title
     * @return string
     */
    protected function _getConfigValue($param)
    {
        return $this->_scopeConfig->getValue(
            'mfcms/index_page/' . $param,
            ScopeInterface::SCOPE_STORE
        );
    }

}
