<?php

namespace Omnyfy\Mcm\Ui\DataProvider\PendingPayout\Grid;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\Search\SearchResultInterface;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as StatusCollectionFactory;

/**
 * Class PendingPayoutOrderDataProvider
 * @package Omnyfy\Mcm\Ui\DataProvider\PendingPayout\Grid
 */
class PendingPayoutOrderDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider {

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
     * @var StatusCollectionFactory
     */
    protected $statusCollectionFactory;

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
        VendorPayout $vendorPayoutResource, 
        HelperData $helper, 
        VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Mcm\Model\Config $config,
        StatusCollectionFactory $statusCollectionFactory,
        array $meta = [], 
        array $data = []
    ) {
        $this->pricing = $pricing;
        $this->vendorPayoutResource = $vendorPayoutResource;
        $this->_helper = $helper;
        $this->vendorRepository = $vendorRepository;
        $this->_config = $config;
        $this->statusCollectionFactory = $statusCollectionFactory;
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
        $statusLabels = $this->statusCollectionFactory->create()->toOptionArray();
        $collection = $this->getSearchResult();

        foreach ($collection as $payout) {
            $payout->setData('grand_total_with_shipping', $this->currency($payout['grand_total_with_shipping']));
            $payout->setData('total_with_shipping', $this->currency($payout['total_with_shipping']));
            $payout->setData('total_category_fee_incl_tax', $this->currency($payout['total_category_fee_incl_tax']));
            $payout->setData('total_seller_fee_incl_tax', $this->currency($payout['total_seller_fee_incl_tax']));
            $payout->setData('total_disbursement_fee_incl_tax', $this->currency($payout['total_disbursement_fee_incl_tax']));
            $payout->setData('payout_shipping', $this->currency($payout['payout_shipping']));
            $payout->setData('order_status', $this->getStatusLabelByCode($payout['status'], $statusLabels));
            // if wholesale is allowed and the supplier is a wholesale supplier, recalculate the amount 
            if ($this->_config->getEnableWholeSale()) {
                if ($this->getVendorPayoutBasisType($payout['vendor_id']) == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
                    $payoutAmountWholesaleVendor = $this->vendorPayoutResource->getTotalOrderByWholesaleVendor($payout['order_id'], $payout['vendor_id']);
                    $payout->setData('payout_amount', $this->currency($payoutAmountWholesaleVendor));
                } else {
                    // This is used to calculate and display the vendor payout amount for Commission vendors in the orders included in payout tab
                    $payoutAmount = $this->vendorPayoutResource->getPayoutAmountCommission($payout['vendor_id'], $payout['order_id'], $payout['payout_amount']);
                    $payout->setData('payout_amount', $this->currency($payoutAmount));
                }
            } else {
                $payout->setData('payout_amount', $this->currency($payout['payout_amount']));
            }
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

    private function getStatusLabelByCode($statusCode, $statusLabels){
        foreach ($statusLabels as $status) {
            if ($statusCode == $status['value']) {
                return $status['label'];
            }
        }
    }
}
