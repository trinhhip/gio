<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Archive;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Cms archive articles list
 */
class PostList extends ArticleList
{
    /**
     * Prepare articles collection
     * @return void
     */
    protected function _prepareArticleCollection()
    {
        parent::_prepareArticleCollection();
        $this->_articleCollection->getSelect()
            ->where('MONTH(publish_time) = ?', $this->getMonth())
            ->where('YEAR(publish_time) = ?', $this->getYear());
    }

    /**
     * Get archive month
     * @return string
     */
    public function getMonth()
    {
        return (int)$this->_coreRegistry->registry('current_cms_archive_month');
    }

    /**
     * Get archive year
     * @return string
     */
    public function getYear()
    {
        return (int)$this->_coreRegistry->registry('current_cms_archive_year');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = $this->_getTitle();
        $this->_addBreadcrumbs($title);
        $this->pageConfig->getTitle()->set($title);
        $this->pageConfig->addRemotePageAsset(
            $this->_url->getUrl(
                $this->getYear() . '-' . str_pad($this->getMonth(), 2, '0', STR_PAD_LEFT),
                Url::CONTROLLER_ARCHIVE
            ),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve title
     * @return string
     */
    protected function _getTitle()
    {
        $time = strtotime($this->getYear() . '-' . $this->getMonth() . '-01');
        return sprintf(
            __('Monthly Archives: %s %s'),
            __(date('F', $time)), date('Y', $time)
        );
    }

    /**
     * Prepare breadcrumbs
     *
     * @param string $title
     * @return void
     * @throws LocalizedException
     */
    protected function _addBreadcrumbs($title)
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
                    'title' => __('Go to Cms Home Page'),
                    'link' => $this->_url->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb('cms_search', ['label' => $title, 'title' => $title]);
        }
    }

}
