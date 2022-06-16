<?php

namespace Omnyfy\Vendor\Ui\DataProvider\Inventory;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\Session;
use Omnyfy\Vendor\Model\Resource\VendorSourceStock;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class InventoryProductDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected $vSourceStockResource;
    protected $getProductSalableQty;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        Session $session,
        VendorSourceStock $vSourceStockResource,
        GetProductSalableQtyInterface $getProductSalableQty,
        array $meta = [],
        array $data = []
    ) {
        $sourceCode = $request->getParam('source_code');
        if ($sourceCode) {
            $data['config']['filter_url_params']['source_code'] = $sourceCode;
        }
        $this->vSourceStockResource = $vSourceStockResource;
        $this->getProductSalableQty = $getProductSalableQty;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $itemData = [];
            foreach ($item->getCustomAttributes() as $attribute) {
                $itemData[$attribute->getAttributeCode()] = $attribute->getValue();
            }
            $sourceStockId = $itemData['source_stock_id'];
            $stockId = $this->vSourceStockResource->getStockIdSourceCode($sourceStockId);
            $stockQty = 0;
            $productType = $item->getTypeId();
            if ($stockId && $productType != 'configurable' && $productType != 'bundle' && $productType != 'grouped') {
                $stockQty = $this->getProductSalableQty->execute($itemData['sku'], $stockId);
            }
            $itemData['stock_qty'] = $stockQty;
            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
}
