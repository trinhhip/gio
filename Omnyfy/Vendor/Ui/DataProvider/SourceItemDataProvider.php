<?php
namespace Omnyfy\Vendor\Ui\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;

class SourceItemDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collection;
        $data['config']['filter_url_params']['source_code'] = $request->getParam('source_code', 0);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    // protected function searchResultToOutput(SearchResultInterface $searchResult)
    // {
    //     $arrItems = [];

    //     $arrItems['items'] = [];
    //     $this->collection->addAttributeToSelect('name', 'joinLeft');
    //     foreach ($searchResult->getItems() as $item) {
    //         $itemData = [];
    //         foreach ($item->getCustomAttributes() as $attribute) {
    //             $itemData[$attribute->getAttributeCode()] = $attribute->getValue();
    //         }
    //         $product = $this->collection->getItemByColumnValue('sku', $item->getCustomAttribute('sku')->getValue());
    //         $itemData['name'] = $product->getName();
    //         $arrItems['items'][] = $itemData;
    //     }

    //     $arrItems['totalRecords'] = $searchResult->getTotalCount();

    //     return $arrItems;
    // }

}
