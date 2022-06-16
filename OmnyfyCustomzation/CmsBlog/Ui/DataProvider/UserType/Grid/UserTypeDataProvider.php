<?php

namespace OmnyfyCustomzation\CmsBlog\Ui\DataProvider\UserType\Grid;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\UserType\Collection;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\UserType\CollectionFactory;

class UserTypeDataProvider extends DataProvider
{

    protected $objectManager;
    protected $userTypeCollection;
    protected $authSession;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name, $primaryFieldName, $requestFieldName, Reporting $reporting, SearchCriteriaBuilder $searchCriteriaBuilder, RequestInterface $request, FilterBuilder $filterBuilder, ObjectManagerInterface $objectManager, CollectionFactory $userTypeCollectionFactory, Session $authSession, array $meta = [], array $data = []
    )
    {
        $this->objectManager = $objectManager;
        $this->userTypeCollection = $userTypeCollectionFactory->create();
        $this->authSession = $authSession;

        parent::__construct(
            $name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data
        );
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
                'user_type',
                'status',
            ]);

        $data = $this->searchResultToOutput($collection);
        return $data;
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
}
