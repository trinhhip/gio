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

class Tabsearch extends Template
{
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

    /**
     * @var DateTime
     */
    protected $_date;

    protected $_filesystem;
    protected $_imageFactory;

    private $_itemPerPage = 8;
    private $_pageFrame = 8;
    private $_curPage = 1;

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

    /**
     * Retrieve prepared Category collection
     *
     * @return OmnyfyCustomzation_CmsBlog_Model_Resource_Category_Collection
     */
    public function getTopicCategoryCollection()
    {
        if (is_null($this->_articleCollection)) {
            #$this->_articleCollection = $this->_getCollection();

            /* $topicId = $this->_getCollection()->addFieldToFilter('title', 'Topic')->getFirstItem()->getCategoryId();

            $this->_categoryCollection = $this->_getCollection();
            $this->_categoryCollection->addFieldToFilter('path', $topicId);
            $this->_categoryCollection->addFieldToFilter('is_learn','1'); */

            #$userType = $this->getData('user_type');
            $userType = $this->getRequest()->getParam('user_tab');
            //get values of current page
            $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
            //get values of current limit
            $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 100;

            $topicId = $this->_dataHelper->getConfig('mfcms/topic_category/topic_category_id');

            $this->_articleCollection = $this->_getCollection()->addFieldToSelect('*')
                ->join(
                    array('article_category_mapping' => 'omnyfy_cms_article_category'),
                    'main_table.category_id = article_category_mapping.category_id',
                    array('category_id' => 'article_category_mapping.category_id')
                )->join(
                    array('user_type' => 'omnyfy_cms_article_user_type'),
                    'article_category_mapping.article_id = user_type.article_id',
                    array('user_type_id' => 'user_type.user_type_id')
                )->join(
                    array('user_type_data' => 'omnyfyCustomzation_cmsblog_user_type'),
                    'user_type_data.id = user_type.user_type_id',
                    array('status' => 'status')
                )->join(
                    array('artcle_data' => 'omnyfy_cms_article'),
                    'article_category_mapping.article_id = artcle_data.article_id',
                    array('article_is_active' => 'artcle_data.is_active')
                );
            $this->_articleCollection->addFieldToFilter('path', $topicId);
            $this->_articleCollection->addFieldToFilter('is_learn', '1');
            if ($userType) {
                $this->_articleCollection->addFieldToFilter('user_type_id', $userType);
            }
            $this->_articleCollection->getSelect()->group('article_category_mapping.category_id');
            $this->_articleCollection->setOrder('position', 'asc');
            $this->_articleCollection->addFieldToFilter('main_table.is_active', '1');
            $this->_articleCollection->addFieldToFilter('user_type_data.status', '1');
            $this->_articleCollection->addFieldToFilter('artcle_data.is_active', '1');
            $this->_articleCollection->addFieldToFilter('publish_time', ['lteq' => $this->_date->gmtDate()]);
            /* $this->_articleCollection->addFieldToFilter('path', $topicId);
            $this->_articleCollection->addFieldToFilter('is_learn', '1'); */
            /*$this->_categoryCollection->addFieldToFilter('end_date', ['gteq' => $this->timezone->date()->format('Y-m-d')]);
            $this->_categoryCollection->setOrder('start_date','asc'); */
            /* $this->_articleCollection->setCurPage($page);
            $this->_articleCollection->setPageSize($pageSize);
            return $this->_articleCollection; */


            /* // search variables
            $articleKeyword = $this->getRequest()->getParam('article_keyword');


            // search by event name
            if(!empty($articleKeyword)){
                $this->_articleCollection->addFieldToFilter(
                                                            array(
                                                                'title',
                                                                'category_snippet'
                                                            ),
                                                            array(
                                                                array('like' => '%'.urldecode($articleKeyword).'%'),
                                                                array('like' => '%'.urldecode($articleKeyword).'%')
                                                            )
                                                        );
            } */
            $this->_articleCollection->addFieldToFilter('main_table.is_active', '1');
            /* $this->_articleCollection->addFieldToFilter('end_date', ['gteq' => $this->timezone->date()->format('Y-m-d')]);
            $this->_articleCollection->setOrder('start_date','asc'); */
        }

        return $this->_articleCollection;
    }

    public function _getCollection()
    {
        $collection = $this->_categorymodelFactory->create();
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

    /**
     * Return URL for resized Article Item image
     *
     * @param OmnyfyCustomzation_CmsBlog_Model_Article $item
     * @param integer $width
     * @return string|false
     */
    public function getImageUrl($item, $width = null, $height = null)
    {
        return $this->_dataHelper->imageResize($item, $width, $height);
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
                array('category_data' => 'omnyfy_cms_category'),
                'category_mapping.category_id = category_data.category_id',
                array('category_is_active' => 'category_data.is_active')
            )
            ->join(
                array('user_type' => 'omnyfy_cms_article_user_type'),
                'main_table.article_id = user_type.article_id',
                array('user_type_id' => 'user_type_id')
            )
            ->join(
                array('user_type_data' => 'omnyfyCustomzation_cmsblog_user_type'),
                'id = user_type.user_type_id',
                array('status' => 'status')
            );
        $articleCount->addFieldToFilter('category_mapping.category_id', $categoryId);
        $articleCount->addFieldToFilter('user_type_id', $userTypeId);
        $articleCount->addFieldToFilter('main_table.is_active', '1');
        $articleCount->addFieldToFilter('category_data.is_active', '1');
        $articleCount->addFieldToFilter('publish_time', ['lteq' => $this->_date->gmtDate()]);
        $articleCount->addFieldToFilter('user_type_data.status', '1');

        if (count($articleCount) > 1) {
            return count($articleCount) . ' articles';
        } else if (count($articleCount) == '1') {
            return count($articleCount) . ' article';
        } else {
            return '0 article';
        }
    }
}
