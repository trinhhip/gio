<?php


namespace OmnyfyCustomzation\CmsBlog\Block\Blog;


use Magento\Catalog\Helper\ImageFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Article\CollectionFactory as ArticleCollectionFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use OmnyfyCustomzation\CmsBlog\Model\TagFactory;

class BlogList extends Template
{
    const ACTIVE = 1;
    /**
     * @var ArticleCollectionFactory
     */
    public $blogFactory;
    /**
     * @var TagCollectionFactory
     */
    public $tagCollection;
    /**
     * @var Repository
     */
    public $assetRepos;
    /**
     * @var ImageFactory
     */
    public $helperImageFactory;
    /**
     * @var CategoryCollectionFactory
     */
    public $categoryCollectionFactory;
    /**
     * @var CategoryFactory
     */
    public $categoryFactory;
    /**
     * @var TagFactory
     */
    public $tagFactory;

    public $cmsPageUrl = null;
    /**
     * @var PageFactory
     */
    public $pageFactory;

    public function __construct(
        ArticleCollectionFactory $blogFactory,
        TagCollectionFactory $tagCollection,
        CategoryCollectionFactory $categoryCollectionFactory,
        Template\Context $context,
        Repository $assetRepos,
        ImageFactory $helperImageFactory,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory,
        PageFactory $pageFactory,
        array $data = []
    )
    {
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
        $this->blogFactory = $blogFactory;
        $this->tagCollection = $tagCollection;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory = $tagFactory;
        $this->pageFactory = $pageFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Vermillion Blog'));
        $collection = $this->getBlogCollection();
        if ($collection) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager'
            )->setAvailableLimit([8 => 8, 11 => 11, 14 => 14, 17 => 17])
                ->setShowPerPage(true)->setCollection(
                    $collection
                );
            $this->setChild('pager', $pager);
            $collection->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getBlogCollection()
    {
        $page = $this->getRequest()->getParam('p', 1);
        $pageSize = $this->getRequest()->getParam('limit', 8);
        $category = $this->getRequest()->getParam('category');
        $tag = $this->getRequest()->getParam('tag');


        $collection = $this->blogFactory->create();
        $collection->addActiveFilter()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('publish_time', 'DESC');

        if ($category) {
            $categoryIds = $this->getCategoryIdsByIdentifier($category);
            $collection->addCategoryFilter($categoryIds);
        }

        if ($tag) {
            $tagIds = $this->getTagIdsByIdentifier($tag);
            $collection->addTagFilter($tagIds);
        }
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    public function getPopularTag()
    {
        $tagCollection = $this->tagCollection->create();
        $tagCollection->getSelect()->join(
            ['art' => 'omnyfy_cms_article_tag'],
            'main_table.tag_id = art.tag_id',
            [
                'count_tag' => 'COUNT(art.tag_id)',
                'tag_id' => 'main_table.tag_id',
                'title' => 'main_table.title',
                'identifier' => 'main_table.identifier',
            ]
        )->group('art.tag_id')
            ->order('count_tag DESC')
            ->limit(3);
        $popularTags = $tagCollection->getData();
        $currentTag = $this->getCurrentTagParam();
        if ($currentTag) {
            $tagActive = [];
            foreach ($popularTags as $key => $popularTag) {
                if ($popularTag['identifier'] == $currentTag) {
                    $tagActive = $popularTag;
                    unset($popularTags[$key]);
                    break;
                }
            }
            array_unshift($popularTags, $tagActive);
        }
        return $popularTags;
    }

    public function getBlogImage($blog)
    {
        $blogImage = $blog->getFeaturedImage();
        if (!$blogImage) {
            $imagePlaceholder = $this->helperImageFactory->create();
            $blogImage = $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('image'));
        }
        return $blogImage;
    }

    public function getCategories()
    {
        $categories = $this->categoryCollectionFactory->create();
        $categories->addFieldToFilter('is_active', self::ACTIVE);
        $categories->addFieldToFilter('use_in_article', self::ACTIVE);
        $categories->addStoreFilter($this->_storeManager->getStore()->getId());
        $categories->setOrder('position', 'ASC');
        $categories->setPageSize(7);
        return $categories;
    }

    public function getTagUrl($tagIdentifier)
    {
        return $this->getPageUrl(['tag' => $tagIdentifier]);
    }

    public function getCategoryUrl($category)
    {
        return $this->getPageUrl(['category' => $category->getIdentifier()]);
    }

    public function getPageUrl($params = [])
    {
        $query = $params ? array_merge($this->getCurrentParams(), $params) : [];
        $urlParams['_query'] = $query;
        $path = $this->getCmsPageUrl();
        return $this->getUrl($path, $urlParams);
    }

    public function getCmsPageUrl()
    {
        if (!$this->cmsPageUrl) {
            $pageId = $this->getRequest()->getParam('page_id');
            $page = $this->pageFactory->create()->load($pageId);
            $this->cmsPageUrl = $page->getIdentifier();
        }
        return $this->cmsPageUrl;
    }

    public function getCategory($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId);
    }

    public function getTag($tagId)
    {
        return $this->tagFactory->create()->load($tagId);
    }

    public function getCurrentCategoryParam()
    {
        return $this->getRequest()->getParam('category');
    }

    public function getCurrentTagParam()
    {
        return $this->getRequest()->getParam('tag');
    }

    public function getCategoryIdsByIdentifier($identifier)
    {
        $categories = $this->categoryCollectionFactory->create();
        $categories->addFieldToFilter('identifier', $identifier);
        return $categories->getAllIds();
    }

    public function getTagIdsByIdentifier($identifier)
    {
        $tags = $this->tagCollection->create();
        $tags->addFieldToFilter('identifier', $identifier);
        return $tags->getAllIds();
    }

    public function clearTag()
    {
        $currentParams = $this->getCurrentParams();
        if (isset($currentParams['tag'])) {
            unset($currentParams['tag']);
        }
        $urlParams['_query'] = $currentParams;
        $path = $this->getCmsPageUrl();
        return $this->getUrl($path, $urlParams);
    }

    protected function getCurrentParams()
    {
        $currentParams = $this->getRequest()->getParams();
        if (isset($currentParams['page_id'])) {
            unset($currentParams['page_id']);
        }
        return $currentParams;
    }
}
