<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article\View;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use OmnyfyCustomzation\CmsBlog\Model\Article;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory;

/**
 * Cms article next and prev article links
 */
class NextPrev extends Template
{
    /**
     * Previous article
     *
     * @var Article
     */
    protected $_prevArticle;

    /**
     * Next article
     *
     * @var Article
     */
    protected $_nextArticle;

    /**
     * @var CollectionFactory
     */
    protected $_articleCollectionFactory;

    /**
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Construct
     *
     * @param Context $context
     * @param CollectionFactory $articleCollectionFactory
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $articleCollectionFactory,
        Registry $coreRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_articleCollectionFactory = $articleCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Retrieve true if need to display next-prev links
     *
     * @return boolean
     */
    public function displayLinks()
    {
        return (bool)$this->_scopeConfig->getValue(
            'mfcms/article_view/nextprev/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve prev article
     * @return Article || bool
     */
    public function getPrevArticle()
    {
        if ($this->_prevArticle === null) {
            $this->_prevArticle = false;
            $collection = $this->_getFrontendCollection()->addFieldToFilter(
                'article_id', [
                    'gt' => $this->getArticle()->getId()
                ]
            );
            $article = $collection->getFirstItem();

            if ($article->getId()) {
                $this->_prevArticle = $article;
            }
        }

        return $this->_prevArticle;
    }

    /**
     * Retrieve article collection with frontend filters and order
     * @return Collection
     * @throws NoSuchEntityException
     */
    protected function _getFrontendCollection()
    {
        $collection = $this->_articleCollectionFactory->create();
        $collection->addActiveFilter()
            ->addFieldToFilter('article_id', ['neq' => $this->getArticle()->getId()])
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('publish_time', 'DESC');
        return $collection;
    }

    /**
     * Retrieve article instance
     *
     * @return Article
     */
    public function getArticle()
    {
        return $this->_coreRegistry->registry('current_cms_article');
    }

    /**
     * Retrieve next article
     * @return Article || bool
     */
    public function getNextArticle()
    {
        if ($this->_nextArticle === null) {
            $this->_nextArticle = false;
            $collection = $this->_getFrontendCollection()->addFieldToFilter(
                'article_id', [
                    'lt' => $this->getArticle()->getId()
                ]
            );
            $article = $collection->getFirstItem();

            if ($article->getId()) {
                $this->_nextArticle = $article;
            }
        }

        return $this->_nextArticle;
    }

}
