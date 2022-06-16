<?php

namespace OmnyfyCustomzation\CmsBlog\Ui\DataProvider\Article\Service;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use OmnyfyCustomzation\CmsBlog\Model\Config\Source\ToolTemplate;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\ToolTemplate\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\ToolTemplate\CollectionFactory;

/**
 * Class ArticleDataProvider
 */
class ToolDataProvider extends DataProvider
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var article
     */
    private $article;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name, $primaryFieldName, $requestFieldName, CollectionFactory $collectionFactory, RequestInterface $request, ReportingInterface $reporting, SearchCriteriaBuilder $searchCriteriaBuilder, FilterBuilder $filterBuilder, ToolTemplate $toolTemplateOption, array $meta = [], array $data = []
    )
    {
        parent::__construct(
            $name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data
        );
        $this->_toolTemplateOption = $toolTemplateOption;
        $this->collection = $collectionFactory->create();
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        /** @var Collection $collection */
        $collection = $this->getSearchResult();

        $collection
            ->addFieldToSelect([
                'id',
                'title',
                'type',
            ]);
        $collection->addFieldToFilter('status', 1);

        foreach ($collection as $tool) {
            $tool->setData('type', $this->getTypeValue($tool['type']));
        }

        $data = $this->searchResultToOutput($collection);
        return $data;
    }

    public function getTypeValue($field)
    {
        $fieldLabels = $this->_toolTemplateOption->toArray();
        return $fieldLabels[$field];
    }

    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];
        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $arrItems['items'][] = $item->getData();
        }

        return $arrItems;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        $this->getCollection()->addFieldToSelect($field, $alias);
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

}
