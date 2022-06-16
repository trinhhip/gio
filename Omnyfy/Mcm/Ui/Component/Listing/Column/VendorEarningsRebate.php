<?php

namespace Omnyfy\Mcm\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\RebateCore\Helper\Calculation as CalculationHelper;

class VendorEarningsRebate extends Column {

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Data
     */
    protected $helper;

    protected $calculationHelper;

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
    CalculationHelper $calculationHelper,
    UrlInterface $urlBuilder, array $components = [],
    array $data = [],
    HelperData $helper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->calculationHelper = $calculationHelper;
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
                if ($item['id']) {
                    $rebateTotal = $this->calculationHelper->sumTotalRebateByVendorAndOrder($item['vendor_id'], $item['order_id']);
                    $item['rebates_deducted'] = $this->currency($rebateTotal);
                }
            }
        }
        return $dataSource;
    }

    public function currency($value) {
        return $this->helper->formatToBaseCurrency($value);
    }
}
