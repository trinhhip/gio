<?php

namespace OmnyfyCustomzation\CmsBlog\Ui\DataProvider\Article\Related;

use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider as DataProvider;
use Magento\Framework\App\RequestInterface;

/**
 * Class ProductDataProvider
 */
class ProductDataProvider extends DataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var article
     */
    private $article;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        ProductLinkRepositoryInterface $productLinkRepository,
        $addFieldStrategies,
        $addFilterStrategies,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->productLinkRepository = $productLinkRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');

        return $collection;
    }

    /**
     * Add specific filters
     *
     * @param Collection $collection
     * @return Collection
     */
    protected function addCollectionFilters(Collection $collection)
    {
        return $collection;
    }

    /**
     * Retrieve article
     *
     * @return ArticleInterface|null
     */
    protected function getArticle()
    {
        if (null !== $this->article) {
            return $this->article;
        }

        if (!($id = $this->request->getParam('current_article_id'))) {
            return null;
        }

        return $this->article = $this->articleRepository->getById($id);
    }

}