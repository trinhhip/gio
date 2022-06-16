<?php


namespace OmnyfyCustomzation\PriceToQuote\Ui\DataProvider\ProductToQuote;

use Magento\Framework\Api\Search\SearchResultInterface;

class GridDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
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
