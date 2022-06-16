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

class TopicSearch extends Template
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

    protected $_filesystem;
    protected $_imageFactory;

    /**
     * @var DateTime
     */
    protected $_date;

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
    public function getArticleCollection()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        if (is_null($this->_articleCollection)) {
            $this->_articleCollection = $this->_getCollection()->addFieldToSelect('*')
                ->join(
                    array('category_mapping' => 'omnyfy_cms_article_category'),
                    'main_table.article_id = category_mapping.article_id',
                    array('category_id' => 'category_id', 'positioncategory' => 'category_mapping.position')
                );
            $this->_articleCollection->addFieldToFilter('category_id', $categoryId);
            $this->_articleCollection->addFieldToFilter('is_active', '1');
            $this->_articleCollection->setOrder('category_mapping.position', 'ASC');
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

    /**
     * Return URL for resized Events Item image
     *
     * @param Omnyfy_Events_Model_Events $item
     * @param integer $width
     * @return string|false
     */
    public function getImageUrl($item, $width, $height)
    {
        return $this->_dataHelper->imageResize($item, $width, $height);
    }

    public function getTopicTabTitle()
    {
        $dataKey = $this->getRequest()->getParam('data_keyword');
        if ($dataKey == 'general') {
            return 'General Information';
        } else if ($dataKey == 'country') {
            return 'For Specific Country';
        } else if ($dataKey == 'tools') {
            return 'Tools & templates';
        }
    }

    public function getTopicIdentifier()
    {
        return $this->getRequest()->getParam('data_keyword');
    }
}
