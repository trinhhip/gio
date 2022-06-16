<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 16:52
 */
namespace Omnyfy\Approval\Ui\DataProvider\Record;

use Magento\Framework\Api\Search\SearchResultInterface;

class Grid extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
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
 