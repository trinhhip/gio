<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column\PendingPayout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\RebateCore\Helper\Calculation;
use Magento\Framework\App\RequestInterface;
use Omnyfy\RebateUI\Helper\Data;
use Omnyfy\RebateCore\Helper\Data as HelperRebateCore;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column
 */
class Payout extends Column
{
    /**
     * @var Invoice
     */
    protected $calculation;

    /**
     * @var RequestInterface
     */
    protected $request;

    protected $helper;

    protected $helperRebateCore;


    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        Calculation $calculation,
        RequestInterface $request,
        UiComponentFactory $uiComponentFactory,
        Data $helper,
        HelperRebateCore $helperRebateCore,
        array $components = [],
        array $data = []
    )
    {
        $this->calculation = $calculation;
        $this->request = $request;
        $this->helper = $helper;
        $this->helperRebateCore = $helperRebateCore;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();
        if (!$this->helperRebateCore->isEnable()) { 
            $this->_data['config']['componentDisabled'] = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $vendorId = $this->request->getParam('vendor_id');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['vendor_id'])) {
                    $receivable = $this->calculation->sumTotalRebatePaidByVendor($item['vendor_id']);
                    $item['rebates_in_payout'] = $this->currency($receivable);
                }
            }
        }

        return $dataSource;
    }

    public function currency($value) {
        return $this->helper->formatToBaseCurrency($value);
    }

}
