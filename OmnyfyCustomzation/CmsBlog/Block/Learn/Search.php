<?php

namespace OmnyfyCustomzation\CmsBlog\Block\Learn;

use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use OmnyfyCustomzation\CmsBlog\Helper\Data;
use OmnyfyCustomzation\CmsBlog\Model\ArticleFactory;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory;

class Search extends Template
{
    public $_curPage = 1;
    /**
     * Article collection
     *
     * @var \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\Collection
     */
    protected $_categoryCollection = null;
    /**
     * Article collection
     *
     * @var Collection
     */
    protected $_articleCollection = null;
    /**
     * Article factory
     *
     * @var CategoryFactory
     */

    protected $_categorymodelFactory;
    /**
     * Article factory
     *
     * @var ArticleFactory
     */
    protected $_articlemodelFactory;
    /** @var Data */
    protected $_dataHelper;
    protected $_filesystem;
    protected $_imageFactory;
    /**
     * @var DateTime
     */
    protected $_date;

    public function __construct(
        Template\Context $context,
        \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\CollectionFactory $categorymodelFactory,
        CollectionFactory $articlemodelFactory,
        AdapterFactory $imageFactory,
        DateTime $date,
        Data $dataHelper,
        array $data = []
    )
    {

        parent::__construct($context, $data);
        $this->_categorymodelFactory = $categorymodelFactory;
        $this->_articlemodelFactory = $articlemodelFactory;
        $this->_filesystem = $context->getFilesystem();
        $this->_imageFactory = $imageFactory;
        $this->_date = $date;
        $this->_dataHelper = $dataHelper;
        $this->_isScopePrivate = true;
    }

    public function getTopicCategoryCollection()
    {
        if (is_null($this->_articleCollection)) {

            $userType = $this->getRequest()->getParam('ut');
            $articleKeyword = $this->getRequest()->getParam('article-search');


            $this->_articleCollection = $this->_getCollection()->addFieldToSelect('*')
                ->join(
                    array('user_type' => 'omnyfy_cms_article_user_type'),
                    'main_table.article_id = user_type.article_id',
                    array('user_type_id' => 'user_type.user_type_id')
                );
            /* $this->_articleCollection->addFieldToFilter('path', $topicId);
            $this->_articleCollection->addFieldToFilter('is_learn', '1'); */
            if ($userType) {
                $this->_articleCollection->addFieldToFilter('user_type_id', $userType);
            }

            // search by article name
            if (!empty($articleKeyword)) {
                #$this->_articleCollection->addFieldToFilter('title', ['like' => '%'.$articleKeyword.'%']);
                $this->_articleCollection->addFieldToFilter(
                    array(
                        'title',
                        'content'
                    ),
                    array(
                        array('like' => '%' . urldecode($articleKeyword) . '%'),
                        array('like' => '%' . urldecode($articleKeyword) . '%')
                    )
                );
            }
            $this->_articleCollection->addFieldToFilter('is_active', '1');
            $this->_articleCollection->addFieldToFilter('publish_time', ['lteq' => $this->_date->gmtDate()]);
        }

        return $this->_articleCollection;
    }

    public function _getCollection()
    {
        $collection = $this->_articlemodelFactory->create();
        return $collection;
    }

    public function getCollection($collection = 'null')
    {
        //get values of current page
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 100;

        if ($collection != 'null') {
            $page = $this->getRequest()->getParam('p');
            if ($page) $this->_curPage = $page;

            $collection->setCurPage($page);
            $collection->setPageSize($pageSize);
            return $collection;
        }
    }


    public function getArticleCount($categoryId, $userTypeId)
    {
        $articleCount = $this->_articlemodelFactory->create()->addFieldToSelect('*')
            ->join(
                array('category_mapping' => 'omnyfy_cms_article_category'),
                'main_table.article_id = category_mapping.article_id',
                array('category_id' => 'category_id')
            )
            ->join(
                array('user_type' => 'omnyfy_cms_article_user_type'),
                'main_table.article_id = user_type.article_id',
                array('user_type_id' => 'user_type_id')
            );
        $articleCount->addFieldToFilter('category_id', $categoryId);
        $articleCount->addFieldToFilter('user_type_id', $userTypeId);
        $articleCount->addFieldToFilter('is_active', '1');
        $articleCount->addFieldToFilter('publish_time', ['lteq' => $this->_date->gmtDate()]);

        if (count($articleCount) > 1) {
            return count($articleCount) . ' articles';
        } else if (count($articleCount) == '1') {
            return count($articleCount) . ' article';
        } else {
            return '0 article';
        }
    }

    /**
     * Return URL for resized CMS Item image
     *
     * @param OmnyfyCustomzation_CmsBlog_Model_Article $item
     * @param integer $width
     * @return string|false
     */
    public function getImageUrl($item, $width, $height)
    {
        return $this->_dataHelper->imageResize($item, $width, $height);
    }
}
