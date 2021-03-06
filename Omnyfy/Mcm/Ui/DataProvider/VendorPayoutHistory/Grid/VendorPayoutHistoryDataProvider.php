<?php

namespace Omnyfy\Mcm\Ui\DataProvider\VendorPayoutHistory\Grid;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\Search\SearchResultInterface;
use Omnyfy\Mcm\Model\VendorPayoutFactory;
use Omnyfy\Mcm\Helper\Data as HelperData;

class VendorPayoutHistoryDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider {

    protected $pricing;

    /**
     * @param string                $name
     * @param string                $primaryFieldName
     * @param string                $requestFieldName
     * @param Reporting             $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface      $request
     * @param FilterBuilder         $filterBuilder
     * @param array                 $meta
     * @param array                 $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
    $name, $primaryFieldName, $requestFieldName, Reporting $reporting, SearchCriteriaBuilder $searchCriteriaBuilder, RequestInterface $request, FilterBuilder $filterBuilder, \Magento\Framework\Pricing\Helper\Data $pricing, VendorPayoutFactory $vendorPayoutFactory, HelperData $helper, array $meta = [], array $data = []
    ) {
        $this->pricing = $pricing;
        $this->vendorPayoutFactory = $vendorPayoutFactory->create();
        $this->_helper = $helper;
        parent::__construct(
                $name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data
        );
    }

    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult) {
        $arrItems = [];
        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $arrItems['items'][] = $item->getData();
        }

        return $arrItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getData() {

        $collection = $this->getSearchResult();
        $collection->addFieldToFilter('fees_charges_id', $this->request->getParam('id'));

        foreach ($collection as $fee) {
            $fee->setData('payout_amount_currency', ($fee['payout_amount']) ? $this->currency($fee['payout_amount']) : '');
        }
        
        return $this->searchResultToOutput($collection);
    }
    public function currency($value) {
        return $this->_helper->formatToBaseCurrency($value);
    }

}
