<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column\VendorReport;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\RequestInterface;
use Omnyfy\RebateUI\Helper\Data;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column
 */
class OrderInfo extends Column
{
    /**
     * @var RequestInterface
     */
    protected $request;

    protected $helper;


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
        RequestInterface $request,
        UiComponentFactory $uiComponentFactory,
        Data $helper,
        array $components = [],
        array $data = []
    )
    {
        $this->request = $request;
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $vendorId = $this->request->getParam('vendor_id');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item['vendor_total_incl_tax'] = $this->currency($item['vendor_total_incl_tax']);
                }
            }
        }

        return $dataSource;
    }

    public function currency($value) {
        return $this->helper->formatToBaseCurrency($value);
    }

}
