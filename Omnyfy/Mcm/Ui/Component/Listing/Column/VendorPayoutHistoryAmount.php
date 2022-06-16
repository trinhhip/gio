<?php

namespace Omnyfy\Mcm\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;
use Omnyfy\Mcm\Model\VendorOrderFactory;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;

class VendorPayoutHistoryAmount extends Column {

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $_mcmHelper;

    protected $resourceVendorPayout;

    protected $vendorOrderFactory;

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
    array $components = [], 
    array $data = [],
    HelperData $helper,
    VendorPayout $resourceVendorPayout,
    VendorRepositoryInterface $vendorRepository,
    VendorOrderFactory $vendorOrderFactory
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_mcmHelper = $helper;
        $this->resourceVendorPayout = $resourceVendorPayout;
        $this->vendorOrderFactory = $vendorOrderFactory;
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
                $vendorOrderModel = $this->vendorOrderFactory->create()->load($item['vendor_order_id']);
                $orderId = $vendorOrderModel->getOrderId();
                $payoutAmount = $item['payout_amount_currency'];
                if ($this->getVendorPayoutBasisType($item['vendor_id']) == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
                    $payoutAmount = $this->resourceVendorPayout->getTotalOrderByWholesaleVendor($orderId, $item['vendor_id']);
                }
                $item['payout_amount_currency'] = $this->currency($payoutAmount);
            }
        }

        return $dataSource;
    }

    public function currency($value) {
        return $this->_mcmHelper->formatToBaseCurrency($value);
    }

    public function getVendorPayoutBasisType($vendorId){
        if ($vendorId) {
            $vendor = $this->vendorRepository->getById($vendorId);
            return $vendor->getPayoutBasisType();
        }
        return false;
    }
}
