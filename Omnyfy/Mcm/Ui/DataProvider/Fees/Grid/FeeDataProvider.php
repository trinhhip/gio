<?php

namespace Omnyfy\Mcm\Ui\DataProvider\Fees\Grid;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\Search\SearchResultInterface;
use Omnyfy\Mcm\Helper\DefaultData;
use Omnyfy\Mcm\Model\FeesChargesFactory;
use Omnyfy\Mcm\Helper\Data as HelperData;

class FeeDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider {

    protected $pricing;
    /**
     * @var DefaultData
     */
    protected $_defaultDataHelper;

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
    $name, $primaryFieldName, $requestFieldName, Reporting $reporting, SearchCriteriaBuilder $searchCriteriaBuilder, RequestInterface $request, FilterBuilder $filterBuilder, \Magento\Framework\Pricing\Helper\Data $pricing, FeesChargesFactory $feesChargesFactory, HelperData $helper, DefaultData $defaultDataHelper, array $meta = [], array $data = []
    ) {
        $this->pricing = $pricing;
        $this->request = $request;
        $this->feesChargesFactory = $feesChargesFactory->create();
        $this->_helper = $helper;
        $this->_defaultDataHelper = $defaultDataHelper;
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

        foreach ($collection as $fee) {
            $fee->setData('vendor_name_status', ($fee['vendor_name']) ? ($fee['vendor_status'] == 0 ? $fee['vendor_name'] . ' (Disabled)' : $fee['vendor_name']) : '');
            $fee->setData('seller_fee_per', ($fee['seller_fee']) ? $fee['seller_fee'] . '%' : '');
            $fee->setData('min_seller_fee_currency', ($fee['min_seller_fee']) ? $this->currency($fee['min_seller_fee']) : $this->currency(0));
            $fee->setData('max_seller_fee_currency', ($fee['max_seller_fee']) ? $this->currency($fee['max_seller_fee']) : $this->currency(0));
            $fee->setData('disbursement_fee_currency', ($fee['disbursement_fee']) ? $this->currency($fee['disbursement_fee']) : $this->currency(0));
            if(!$fee->getData('tax_name')){
                $fee->setData('tax_name', $this->_defaultDataHelper->getGeneralConfig(DefaultData::DEFAULT_VENDOR_TAX_NAME));
            }
            if($fee->getData('tax_rate') == 0 || !$fee->getData('tax_rate')){
                $fee->setData('tax_rate', $this->_defaultDataHelper->getGeneralConfig(DefaultData::DEFAULT_VENDOR_TAX_RATE));
            }
        }
        $collection->getSelect();

        return $this->searchResultToOutput($collection);
    }

    public function currency($value) {
        return $this->_helper->formatToBaseCurrency($value);
    }

}
