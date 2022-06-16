<?php

/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Category model
 *
 * @method \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category _getResource()
 * @method \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category getResource()
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method string getTitle()
 * @method $this setTitle(string $value)
 * @method string getMetaKeywords()
 * @method $this setMetaKeywords(string $value)
 * @method $this setMetaDescription(string $value)
 * @method string getIdentifier()
 * @method $this setIdentifier(string $value)
 */
class Category extends AbstractModel
{

    /**
     * Category's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'omnyfy_cms_category';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'cms_category';

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Url $url
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context, Registry $registry, Url $url, \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory $articleCollectionFactory, AbstractResource $resource = null, AbstractDb $resourceCollection = null, array $data = []
    )
    {
        $this->_url = $url;
        $this->_articleCollectionFactory = $articleCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve model title
     * @param boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Category' : 'Categories';
    }

    /**
     * Retrieve true if category is active
     * @return boolean [description]
     */
    public function isActive()
    {
        return ($this->getStatus() == self::STATUS_ENABLED);
    }

    /**
     * Retrieve available category statuses
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    /**
     * Check if category identifier exist for specific store
     * return category id if category exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Check if category identifier exist for specific store
     * return category count if matched
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifierCount($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifierCount($identifier, $storeId);
    }

    /**
     * Retrieve parent category
     * @return self || false
     */
    public function getParentCategory()
    {
        $k = 'parent_category';
        if (!$this->hasData($k)) {

            if ($pId = $this->getParentId()) {
                $category = clone $this;
                $category->load($pId);

                if ($category->getId()) {
                    $this->setData($k, $category);
                }
            }
        }

        if ($category = $this->getData($k)) {
            if ($category->isVisibleOnStore($this->getStoreId())) {
                return $category;
            }
        }

        return false;
    }

    /**
     * Retrieve parent category id
     * @return array
     */
    public function getParentId()
    {
        $parentIds = $this->getParentIds();
        if ($parentIds) {
            return $parentIds[count($parentIds) - 1];
        }

        return 0;
    }

    /**
     * Retrieve parent category ids
     * @return array
     */
    public function getParentIds()
    {
        $k = 'parent_ids';
        if (!$this->hasData($k)) {
            $this->setData($k, $this->getPath() ? explode('/', $this->getPath()) : []
            );
        }

        return $this->getData($k);
    }

    /**
     * Retrieve children category ids
     * @return array
     */
    public function getChildrenIds()
    {
        $k = 'children_ids';
        if (!$this->hasData($k)) {

            $categories = ObjectManager::getInstance()
                ->create($this->_collectionName);

            $ids = [];
            foreach ($categories as $category) {
                if ($category->isParent($this)) {
                    $ids[] = $category->getId();
                }
            }

            $this->setData($k, $ids
            );
        }

        return $this->getData($k);
    }

    /**
     * Check if current category is child category
     * @param self $category
     * @return boolean
     */
    public function isChild($category)
    {
        return $category->isParent($this);
    }

    /**
     * Check if current category is parent category
     * @param self $category
     * @return boolean
     */
    public function isParent($category)
    {
        if (is_object($category)) {
            $category = $category->getId();
        }

        return in_array($category, $this->getParentIds());
    }

    /**
     * Retrieve category depth level
     * @return int
     */
    public function getLevel()
    {
        return count($this->getParentIds());
    }

    /**
     * Retrieve catgegory url route path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_CATEGORY);
    }

    /**
     * Retrieve category url
     * @return string
     */
    public function getCategoryUrl()
    {
        return $this->_url->getUrl($this, URL::CONTROLLER_CATEGORY);
    }

    /**
     * Retrieve meta title
     * @return string
     */
    public function getMetaTitle()
    {
        $title = $this->getData('meta_title');
        if (!$title) {
            $title = $this->getData('title');
        }

        return trim($title);
    }

    /**
     * Retrieve meta description
     * @return string
     */
    public function getMetaDescription()
    {
        $desc = $this->getData('meta_description');
        if (!$desc) {
            $desc = $this->getData('content');
        }

        $desc = strip_tags($desc);
        if (mb_strlen($desc) > 160) {
            $desc = mb_substr($desc, 0, 160);
        }

        return trim($desc);
    }

    /**
     * Retrieve if is visible on store
     * @return bool
     */
    public function isVisibleOnStore($storeId)
    {
        return $this->getIsActive() && array_intersect([0, $storeId], $this->getStoreIds());
    }

    /**
     * Retrieve category icon url
     * @return string
     */
    public function getCategoryIcon()
    {
        if ($file = $this->getData('category_icon')) {
            $image = $this->_url->getMediaUrl($file);
        } else {
            $image = false;
        }
        $this->setData('category_icon', $image);

        return $this->getData('category_icon');
    }

    /**
     * Retrieve category icon url
     * @return string
     */
    public function getCategoryIconFrontend()
    {
        if ($file = $this->getData('category_icon')) {
            $image = $this->_url->getMediaUrl($file);
        } else {
            $image = false;
        }
        $this->setData('category_icon', $image);

        return $file;
    }

    /**
     * Retrieve category icon url
     * @return string
     */
    public function getCategoryBanner()
    {
        if ($file = $this->getData('category_banner')) {
            $image = $this->_url->getMediaUrl($file);
        } else {
            $image = false;
        }
        $this->setData('category_banner', $image);

        return $this->getData('category_banner');
    }

    /**
     * Retrieve category icon url
     * @return string
     */
    public function getCategoryBannerFrontend()
    {
        if ($file = $this->getData('category_banner')) {
            $image = $this->_url->getMediaUrl($file);
        } else {
            $image = false;
        }
        $this->setData('category_banner', $image);

        return $file;
    }

    /**
     * Retrieve article related products
     * @return CollectionFactory
     */
    public function getCategoryArticles()
    {
        if (!$this->hasData('category_articles')) {


            $collection = $this->_articleCollectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['rl' => $this->getResource()->getTable('omnyfy_cms_article_category')], 'main_table.article_id = rl.article_id', ['position']
            )->where(
                'rl.category_id = ?', $this->getId()
            );

            $this->setData('category_articles', $collection);
        }
        return $this->getData('category_articles');
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category');
    }

}
