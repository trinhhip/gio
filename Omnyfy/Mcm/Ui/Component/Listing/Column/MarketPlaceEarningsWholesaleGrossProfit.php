<?php

namespace Omnyfy\Mcm\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;

class MarketPlaceEarningsWholesaleGrossProfit extends Column {

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $vendorFeeReportFactory;

    protected $vendorRepository;

    protected $helper;

    protected $resourceVendorPayout;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
    ContextInterface $context,
    UiComponentFactory $uiComponentFactory,
    UrlInterface $urlBuilder,
    VendorRepositoryInterface $vendorRepository,
    HelperData $helper,
    VendorPayout $resourceVendorPayout,
    array $components = [],
    array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->vendorRepository = $vendorRepository;
        $this->helper = $helper;
        $this->resourceVendorPayout = $resourceVendorPayout;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
                $payoutBasisType = $this->getVendorPayoutBasisType($item['vendor_id']);
                $payoutAmount = $this->resourceVendorPayout->getTotalOrderByWholesaleVendor($item['order_id'], $item['vendor_id']);

                if ($payoutBasisType == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
                    $grossProfit = $item['subtotal_incl_tax'] - $payoutAmount;
                    $item['wholesale_order_gross_profit'] = $this->currency($grossProfit);
                }
            }
        }
		return $dataSource;
    }

    public function getVendorPayoutBasisType($vendorId){
        if ($vendorId) {
            $vendor = $this->vendorRepository->getById($vendorId);
            return $vendor->getPayoutBasisType();
        }
        return false;
    }

    public function currency($value) {
        return $this->helper->formatToBaseCurrency($value);
    }
}
