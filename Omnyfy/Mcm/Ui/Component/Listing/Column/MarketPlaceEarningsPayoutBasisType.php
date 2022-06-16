<?php

namespace Omnyfy\Mcm\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;

class MarketPlaceEarningsPayoutBasisType extends Column {

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $vendorFeeReportFactory;

    protected $vendorRepository;

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
    array $components = [], 
    array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->vendorRepository = $vendorRepository;
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
                if ($payoutBasisType == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
                    $item['payout_basis_type'] = "Wholesale Vendor";
                } elseif ($payoutBasisType == PayoutBasisType::COMMISSION_VENDOR_VALUE) {
                    $item['payout_basis_type'] = "Commission Vendor";
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
}