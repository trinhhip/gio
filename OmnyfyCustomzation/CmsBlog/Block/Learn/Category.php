<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Learn;

use Magento\Cms\Model\Page;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use OmnyfyCustomzation\CmsBlog\Helper\Data;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory;

class Category extends Template
{
    /**
     * Cms collection
     *
     * @var \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\Collection
     */
    protected $_categoryCollection = null;

    /**
     * Cms collection
     *
     * @var Collection
     */
    protected $_articleCollection = null;

    /**
     * Cms factory
     *
     * @var CategoryFactory
     */
    protected $_categorymodelFactory;
    /**
     * Cms factory
     *
     * @var CategoryFactory
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
    protected $_cmsPage;

    public function __construct(
        Template\Context $context,
        \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\CollectionFactory $categorymodelFactory,
        CollectionFactory $articlemodelFactory,
        AdapterFactory $imageFactory,
        DateTime $date,
        Data $dataHelper,
        Page $cmsPage,
        array $data = []
    )
    {

        parent::__construct($context, $data);
        $this->_cmsPage = $cmsPage;
        $this->_categorymodelFactory = $categorymodelFactory;
        $this->_articlemodelFactory = $articlemodelFactory;
        $this->_imageFactory = $imageFactory;
        $this->_filesystem = $context->getFilesystem();
        $this->_date = $date;
        $this->_dataHelper = $dataHelper;
        $this->_isScopePrivate = true;
    }

    /**
     * Return URL for resized Article Item image
     *
     * @param $item
     * @param integer $width
     * @param null $height
     * @return string|false
     */
    public function getImageUrl($item, $width = null, $height = null)
    {
        return $this->_dataHelper->imageResize($item, $width, $height);
    }


    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getArticleCount($categoryId)
    {
        $userType = $this->getRequest()->getParam('usertype');
        $articleCount = $this->_articlemodelFactory->create()->addFieldToSelect('*')
            ->join(
                array('category_mapping' => 'omnyfy_cms_article_category'),
                'main_table.article_id = category_mapping.article_id',
                array('category_id' => 'category_id')
            )->join(
                array('user_type' => 'omnyfy_cms_article_user_type'),
                'main_table.article_id = user_type.article_id',
                array('user_type_id' => 'user_type.user_type_id')
            )->join(
                array('user_type_data' => 'omnyfyCustomzation_cmsblog_user_type'),
                'user_type_data.id = user_type.user_type_id',
                array('status' => 'status')
            )->join(
                array('category_data' => 'omnyfy_cms_category'),
                'category_mapping.category_id = category_data.category_id',
                array('category_is_active' => 'category_data.is_active')
            );
        $articleCount->addFieldToFilter('category_data.category_id', $categoryId);
        if ($userType) {
            $articleCount->addFieldToFilter('user_type_id', $userType);
        }
        $articleCount->addFieldToFilter('main_table.is_active', '1');
        $articleCount->addFieldToFilter('user_type_data.status', '1');
        $articleCount->addFieldToFilter('category_data.is_active', '1');
        $articleCount->addFieldToFilter('publish_time', ['lteq' => $this->_date->gmtDate()]);
        $articleCount->getSelect()->group('main_table.article_id');

        if (count($articleCount) > 1) {
            return count($articleCount) . ' articles';
        } else if (count($articleCount) == '1') {
            return count($articleCount) . ' article';
        } else {
            return '0 article';
        }
    }

    public function getCurrentPageId()
    {
        return $this->_cmsPage->getId();
    }

    /**
     * @return string
     */
    // method for get pager html
    protected function _prepareLayout()
    {
        $collection = $this->getCollection();

        parent::_prepareLayout();

        return $this;
    }

    public function getCollection()
    {
        $userType = $this->getRequest()->getParam('usertype');

        $topicId = $this->_dataHelper->getConfig('mfcms/topic_category/topic_category_id');

        $cateId = $this->getRequest()->getParam('id');

        $this->_categoryCollection = $this->_getCollection()->addFieldToSelect('*')
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
        $this->_categoryCollection->addFieldToFilter('path', $topicId);
        $this->_categoryCollection->addFieldToFilter('is_learn', '1');

        if ($userType) {
            $this->_categoryCollection->addFieldToFilter('user_type_id', $userType);
        }

        $this->_categoryCollection->setOrder('position', 'asc');
        $this->_categoryCollection->addFieldToFilter('category_id', array('neq' => $cateId));
        $this->_categoryCollection->addFieldToFilter('main_table.is_active', '1');
        $this->_categoryCollection->addFieldToFilter('user_type_data.status', '1');
        $this->_categoryCollection->addFieldToFilter('artcle_data.is_active', '1');
        $this->_categoryCollection->getSelect()->group('main_table.category_id');

        /* $this->_categoryCollection->setCurPage($page);
        $this->_categoryCollection->setPageSize($pageSize); */
        return $this->_categoryCollection;
    }

    public function _getCollection()
    {
        return $this->_categorymodelFactory->create();
    }
}
