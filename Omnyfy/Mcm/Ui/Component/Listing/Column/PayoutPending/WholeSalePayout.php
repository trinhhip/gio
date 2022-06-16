<?php

namespace Omnyfy\Mcm\Ui\Component\Listing\Column\PayoutPending;

use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType as PayoutBasisTypeOptions;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;

/**
 * Class WholeSalePayout
 * @package Omnyfy\Vendor\Ui\Component\Listing\Column
 */
class WholeSalePayout extends Column
{
    /**
     * @var PayoutBasisTypeOptions
     */
    protected $payoutBasisType;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var VendorPayout
     */
    protected $vendorPayout;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * Telephone constructor.
     * 
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PayoutBasisTypeOptions $payoutBasisType,
        \Magento\Framework\App\Request\Http $request,
        VendorRepositoryInterface $vendorRepository,
        VendorPayout $vendorPayout,
        HelperData $helper, 
        array $components = [],
        array $data = []
    ) {
        $this->payoutBasisType = $payoutBasisType;
        $this->request = $request;
        $this->vendorRepository = $vendorRepository;
        $this->vendorPayout = $vendorPayout;
        $this->helper = $helper;
        if (!$this->isWholeSaleVendor()) {
            $data = [];
        }
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($this->isWholeSaleVendor()) {
                    $payoutAmountWholesaleVendor = $this->getWholeSalePayoutTotal($item['order_id'], $item['vendor_id']);
                    $this->currency($payoutAmountWholesaleVendor);
                    $item['wholesale_payout_price_total'] = $this->currency($payoutAmountWholesaleVendor);
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getVendorPayoutBasisType(){
        $vendorId = $this->request->getParam('vendor_id');
        if ($vendorId) {
            $vendor = $this->vendorRepository->getById($vendorId);
            return $vendor->getPayoutBasisType();
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isWholeSaleVendor(){
        $vendorId = $this->request->getParam('vendor_id');
        if ($this->getVendorPayoutBasisType($vendorId) == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
            return true;
        }
        return false;
    }

    public function currency($value) {
        return $this->helper->formatToBaseCurrency($value);
    }

    public function getWholeSalePayoutTotal($orderId, $vendorId){
        $total = $this->vendorPayout->getPayoutTotalWholesaleVendor($orderId, $vendorId);
        if(!empty($total)){
            return $this->currency($total);
        }
        return $this->currency(0);
    }

}