<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Article\ArticleList;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Abstract cms article list block
 */
abstract class AbstractList extends Template
{
    /**
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var Page
     */
    protected $_article;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var CollectionFactory
     */
    protected $_articleCollectionFactory;

    /**
     * @var Collection
     */
    protected $_articleCollection;

    /**
     * @var Url
     */
    protected $_url;

    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FilterProvider $filterProvider
     * @param CollectionFactory $articleCollectionFactory
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FilterProvider $filterProvider,
        CollectionFactory $articleCollectionFactory,
        Url $url,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_filterProvider = $filterProvider;
        $this->_articleCollectionFactory = $articleCollectionFactory;
        $this->_url = $url;
    }

    /**
     * Prepare articles collection
     *
     * @return Collection
     */
    public function getArticleCollection()
    {
        if (is_null($this->_articleCollection)) {
            $this->_prepareArticleCollection();
        }

        return $this->_articleCollection;
    }

    /**
     * Prepare articles collection
     *
     * @return void
     */
    protected function _prepareArticleCollection()
    {
        $this->_articleCollection = $this->_articleCollectionFactory->create()
            ->addActiveFilter()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('publish_time', 'DESC');

        if ($this->getPageSize()) {
            $this->_articleCollection->setPageSize($this->getPageSize());
        }
    }

}
