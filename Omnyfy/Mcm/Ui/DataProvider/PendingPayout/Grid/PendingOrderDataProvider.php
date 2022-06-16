<?php

namespace Omnyfy\Mcm\Ui\DataProvider\PendingPayout\Grid;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\Search\SearchResultInterface;
use Omnyfy\Mcm\Api\VendorPayoutInterface;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;

/**
 * Class PendingOrderDataProvider
 * @package Omnyfy\Mcm\Ui\DataProvider\PendingPayout\Grid
 */
class PendingOrderDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider {

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricing;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Omnyfy\Mcm\Model\Config
     */
    protected $_config;

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
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\Framework\Pricing\Helper\Data $pricing,
        VendorPayoutInterface $vendorPayoutResource,
        HelperData $helper,
        \Omnyfy\Mcm\Model\Config $config,
        array $meta = [],
        array $data = []
    ) {
        $this->pricing = $pricing;
        $this->vendorPayoutResource = $vendorPayoutResource;
        $this->_helper = $helper;
        $this->_config = $config;
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

        foreach ($collection as $payout) {
            // if wholesale is allowed and the supplier is a wholesale supplier, recalculate the amount
            $payoutAmount = $this->vendorPayoutResource->getPayoutAmount($payout['vendor_id'], $payout['order_id']);
            $payout->setData('payout_amount', $this->currency($payoutAmount));
            $payout->setData('grand_total_with_shipping', $this->currency($payout['grand_total_with_shipping']));
            $payout->setData('total_with_shipping', $this->currency($payout['total_with_shipping']));
            $payout->setData('total_category_fee_incl_tax', $this->currency($payout['total_category_fee_incl_tax']));
            $payout->setData('total_seller_fee_incl_tax', $this->currency($payout['total_seller_fee_incl_tax']));
            $payout->setData('total_disbursement_fee_incl_tax', $this->currency($payout['total_disbursement_fee_incl_tax']));
            $payout->setData('payout_amount', $this->currency($payoutAmount));
            $payout->setData('payout_shipping', $this->currency($payout['payout_shipping']));
        }

        return $this->searchResultToOutput($collection);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function currency($value) {
        return $this->_helper->formatToBaseCurrency($value);
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorPayoutBasisType($vendorId){
        $vendor = $this->vendorRepository->getById($vendorId);
        return $vendor->getPayoutBasisType();
    }

}
