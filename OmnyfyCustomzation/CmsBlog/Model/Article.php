<?php

namespace OmnyfyCustomzation\CmsBlog\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use OmnyfyCustomzation\CmsBlog\Model\AuthorFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\UserType\Collection;

/**
 * Article model
 *
 * @method ResourceModel\Article _getResource()
 * @method ResourceModel\Article getResource()
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method string getTitle()
 * @method $this setTitle(string $value)
 * @method string getMetaKeywords()
 * @method $this setMetaKeywords(string $value)
// * @method string getMetaDescription()
 * @method $this setMetaDescription(string $value)
 * @method string getIdentifier()
 * @method $this setIdentifier(string $value)
 * @method string getContent()
 * @method $this setContent(string $value)
 * @method string getContentHeading()
 * @method $this setContentHeading(string $value)
 */
class Article extends AbstractModel
{
    /**
     * Articles's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Base media folder path
     */
    const BASE_MEDIA_PATH = 'omnyfy_cms';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'omnyfy_cms_article';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'cms_article';

    /**
     * @var Url
     */
    protected $_url;

    /**
     * @var AuthorFactory
     */
    protected $_authorFactory;

    /**
     * @var ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var ResourceModel\Tag\CollectionFactory
     */
    protected $_tagCollectionFactory;

    /**
     * @var ResourceModel\UserType\CollectionFactory
     */
    protected $_userTypeCollectionFactory;


    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Omnyfy\Vendor\Model\Resource\Location\CollectionFactory
     */
    protected $serviceCollectionFactory;

    /**
     * @var \Omnyfy\Vendor\Model\Resource\ToolTemplate\CollectionFactory
     */
    protected $toolCollectionFactory;

    /**
     * @var ResourceModel\Category\Collection
     */
    protected $_parentCategories;

    /**
     * @var ResourceModel\Tag\Collection
     */
    protected $_relatedTags;

    /**
     * @var Collection
     */
    protected $_relatedUserTypes;

    /**
     * @var ResourceModel\Article\Collection
     */
    protected $_relatedArticlesCollection;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Url $url
     * @param AuthorFactory $authorFactory
     * @param ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param ResourceModel\Tag\CollectionFactory $tagCollectionFactory
     * @param ResourceModel\UserType\CollectionFactory $userTypeCollectionFactory
     * @param CollectionFactory $productCollectionFactory
     * @param \Omnyfy\Vendor\Model\Resource\Location\CollectionFactory $serviceCollectionFactory
     * @param ResourceModel\ToolTemplate\CollectionFactory $toolCollectionFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Url $url,
        AuthorFactory $authorFactory,
        ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        ResourceModel\Tag\CollectionFactory $tagCollectionFactory,
        ResourceModel\UserType\CollectionFactory $userTypeCollectionFactory,
        CollectionFactory $productCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\Location\CollectionFactory $serviceCollectionFactory,
        ResourceModel\ToolTemplate\CollectionFactory $toolCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_url = $url;
        $this->_authorFactory = $authorFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_tagCollectionFactory = $tagCollectionFactory;
        $this->_userTypeCollectionFactory = $userTypeCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->toolCollectionFactory = $toolCollectionFactory;

        $this->_relatedArticlesCollection = clone($this->getCollection());
    }

    /**
     * Retrieve model title
     * @param boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Article' : 'Articles';
    }

    /**
     * Retrieve true if article is active
     * @return boolean [description]
     */
    public function isActive()
    {
        return ($this->getStatus() == self::STATUS_ENABLED);
    }

    /**
     * Retrieve available article statuses
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    /**
     * Check if article identifier exist for specific store
     * return article id if article exists
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
     * Retrieve article url route path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_POST);
    }

    /**
     * Retrieve article url
     * @return string
     */
    public function getArticleUrl()
    {
        return $this->_url->getUrl($this, URL::CONTROLLER_POST);
    }

    /**
     * Retrieve featured image url
     * @return string
     */
    public function getFeaturedImage()
    {
        if (!$this->hasData('featured_image')) {
            if ($file = $this->getData('featured_img')) {
                $image = $this->_url->getMediaUrl($file);
            } else {
                $image = false;
            }
            $this->setData('featured_image', $image);
        }

        return $this->getData('featured_image');
    }

    /**
     * Retrieve og title
     * @return string
     */
    public function getOgTitle()
    {
        $title = $this->getData('og_title');
        if (!$title) {
            $title = $this->getMetaTitle();
        }

        return trim($title);
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
     * Retrieve og description
     * @return string
     */
    public function getOgDescription()
    {
        $desc = $this->getData('og_description');
        if (!$desc) {
            $desc = $this->getMetaDescription();
        } else {
            $desc = strip_tags($desc);
            if (mb_strlen($desc) > 160) {
                $desc = mb_substr($desc, 0, 160);
            }
        }

        return trim($desc);
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
     * Retrieve og type
     * @return string
     */
    public function getOgType()
    {
        $type = $this->getData('og_type');
        if (!$type) {
            $type = 'article';
        }

        return trim($type);
    }

    /**
     * Retrieve og image url
     * @return string
     */
    public function getOgImage()
    {
        if (!$this->hasData('og_image')) {

            if ($file = $this->getData('og_img')) {
                $image = $this->_url->getMediaUrl($file);
            } else {
                $image = false;
            }
            $this->setData('og_image', $image);
        }

        return $this->getData('og_image');
    }

    /**
     * Retrieve article parent categories count
     * @return int
     */
    public function getCategoriesCount()
    {
        return count($this->getParentCategories());
    }

    /**
     * Retrieve article parent categories
     * @return ResourceModel\Category\Collection
     */
    public function getParentCategories()
    {
        if (is_null($this->_parentCategories)) {
            $this->_parentCategories = $this->_categoryCollectionFactory->create()
                ->addFieldToFilter('category_id', ['in' => $this->getCategories()])
                ->addStoreFilter($this->getStoreId())
                ->addActiveFilter()
                ->setOrder('position');
        }

        return $this->_parentCategories;
    }

    /**
     * Retrieve article tags count
     * @return int
     */
    public function getTagsCount()
    {
        return count($this->getRelatedTags());
    }

    /**
     * Retrieve article tags
     * @return ResourceModel\Tag\Collection
     */
    public function getRelatedTags()
    {
        if (is_null($this->_relatedTags)) {
            $this->_relatedTags = $this->_tagCollectionFactory->create()
                ->addFieldToFilter('tag_id', ['in' => $this->getTags()])
                ->setOrder('title');
        }

        return $this->_relatedTags;
    }

    /**
     * Retrieve article userTypes count
     * @return int
     */
    public function getUserTypesCount()
    {
        return count($this->getRelatedUserTypes());
    }

    /**
     * Retrieve article userTypes
     * @return Collection
     */
    public function getRelatedUserTypes()
    {
        if (is_null($this->_relatedUserTypes)) {
            $this->_relatedUserTypes = $this->_userTypeCollectionFactory->create()
                ->addFieldToFilter('id', ['in' => $this->getUserTypes()])
                ->setOrder('user_type');
        }

        return $this->_relatedUserTypes;
    }

    /**
     * Retrieve article related articles
     * @return ResourceModel\Article\Collection
     */
    public function getRelatedArticles()
    {
        if (!$this->hasData('related_articles')) {
            $collection = $this->_relatedArticlesCollection
                ->addFieldToFilter('article_id', ['neq' => $this->getId()])
                ->addStoreFilter($this->getStoreId());
            $collection->getSelect()->joinLeft(
                ['rl' => $this->getResource()->getTable('omnyfy_cms_article_relatedarticle')],
                'main_table.article_id = rl.related_id',
                ['position']
            )->where(
                'rl.article_id = ?',
                $this->getId()
            );
            $this->setData('related_articles', $collection);
        }

        return $this->getData('related_articles');
    }

    /**
     * Retrieve article userTypes
     * @return Collection
     */
//    public function getRelatedServiceCategory()
//    {
//        if (is_null($this->_relatedUserTypes)) {
//            $this->_relatedUserTypes = $this->_userTypeCollectionFactory->create()
//                ->addFieldToFilter('id', ['in' => $this->getUserTypes()])
//                ->setOrder('position');
//        }
//
//        return $this->_relatedUserTypes;
//    }
//
//    /**
//     * Retrieve article userTypes count
//     * @return int
//     */
//    public function getServiceCategoryCount()
//    {
//        return count($this->getRelatedServiceCategory());
//    }

    /**
     * Retrieve article related products
     * @return CollectionFactory
     */
    public function getRelatedProducts()
    {
        if (!$this->hasData('related_products')) {
            $collection = $this->_productCollectionFactory->create();

            if ($this->getStoreId()) {
                $collection->addStoreFilter($this->getStoreId());
            }

            $collection->getSelect()->joinLeft(
                ['rl' => $this->getResource()->getTable('omnyfy_cms_article_relatedproduct')],
                'e.entity_id = rl.related_id',
                ['position']
            )->where(
                'rl.article_id = ?',
                $this->getId()
            );

            $this->setData('related_products', $collection);
        }

        return $this->getData('related_products');
    }

    /**
     * Retrieve article related services
     * @return CollectionFactory
     */
    public function getRelatedServices()
    {
        if (!$this->hasData('related_services')) {
            $collection = $this->serviceCollectionFactory->create();

            $collection->getSelect()->joinLeft(
                ['rl' => $this->getResource()->getTable('omnyfy_cms_article_vendor')],
                'e.entity_id = rl.vendor_id',
                ['position']
            )->joinLeft(
                ['vve' => $this->getResource()->getTable('omnyfy_vendor_vendor_entity')],
                'e.vendor_id = vve.entity_id',
                ['name']
            )->where(
                'rl.article_id = ?',
                $this->getId()
            );

            $this->setData('related_services', $collection);
        }

        return $this->getData('related_services');
    }

    /**
     * Retrieve article related tool
     * @return CollectionFactory
     */
    public function getRelatedTools()
    {
        if (!$this->hasData('related_tools')) {
            $collection = $this->toolCollectionFactory->create();

            if ($this->getStoreId()) {
                $collection->addStoreFilter($this->getStoreId());
            }

            $collection->getSelect()->joinLeft(
                ['rl' => $this->getResource()->getTable('omnyfy_cms_article_tool_template')],
                'main_table.id = rl.tool_template_id',
                ['position']
            )
//                    ->joinLeft(
//                ['tt' => $this->getResource()->getTable('omnyfy_cms_tool_template')],
//                'e.vendor_id = tt.id',
//                ['name']
//            )
                ->where(
                    'rl.article_id = ?',
                    $this->getId()
                );

            $this->setData('related_tools', $collection);
        }

        return $this->getData('related_tools');
    }

    /**
     * Retrieve article author
     * @return Author | false
     */
    public function getAuthor()
    {
        if (!$this->hasData('author')) {
            $author = false;
            if ($authorId = $this->getData('author_id')) {
                $_author = $this->_authorFactory->create();
                $_author->load($authorId);
                if ($_author->getId()) {
                    $author = $_author;
                }
            }
            $this->setData('author', $author);
        }
        return $this->getData('author');
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
     * Retrieve article publish date using format
     * @param string $format
     * @return string
     */
    public function getPublishDate($format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($this->getData('publish_time')));
    }

    /**
     * Retrieve article publish date using format
     * @param string $format
     * @return string
     */
    public function getUpdateDate($format = 'Y-m-d H:i:s')
    {
        return date($format, strtotime($this->getData('update_time')));
    }

    /**
     * Temporary method to get images from some custom cms version. Do not use this method.
     * @param string $format
     * @return string
     */
    public function getArticleImage()
    {
        $image = $this->getData('featured_img');
        if (!$image) {
            $image = $this->getData('article_image');
        }
        return $image;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article');
    }

}
