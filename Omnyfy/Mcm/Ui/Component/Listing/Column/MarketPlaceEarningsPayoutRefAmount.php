<?php

namespace Omnyfy\Mcm\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Api\VendorPayoutInterface;

class MarketPlaceEarningsPayoutRefAmount extends Column {

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var VendorPayoutInterface
     */
    protected $vendorPayoutInterface;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        HelperData $helper,
        VendorPayoutInterface $vendorPayoutInterface
    ) {
        $this->helper = $helper;
        $this->vendorPayoutInterface = $vendorPayoutInterface;
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
                $payoutAmount = $this->vendorPayoutInterface->getPayoutAmount($item['vendor_id'], $item['order_id']);
                if ($payoutAmount) {
                    $item['payout_amount'] = $this->currency($payoutAmount);
                }
            }
        }
        return $dataSource;
    }

    public function currency($value) {
        return $this->helper->formatToBaseCurrency($value);
    }
}
