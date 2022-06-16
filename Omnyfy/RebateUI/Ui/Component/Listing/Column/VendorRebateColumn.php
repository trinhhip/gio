<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\RebateUI\Helper\Data;
use Omnyfy\RebateCore\Helper\Calculation;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column
 */
class VendorRebateColumn extends Column
{
    /**
     * Url path
     */
    const URL_PATH_VENDOR_REPORT = 'rebate_ui/vendorRebateReport/index';
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $helper;

    protected $calculation;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Calculation $calculation,
        Data $helper,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->calculation = $calculation;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {  
                $url = $this->urlBuilder->getUrl(static::URL_PATH_VENDOR_REPORT,['vendor_id' => $items['entity_id']]);
                $items['total_rebate'] = html_entity_decode($this->getHtmlEmailInvoice($url, $items['entity_id']));
            }
        }

        return $dataSource;
    }

    public function getHtmlEmailInvoice($url, $vendorId){
        return '<a href="' . $url . '" title="Total Earned">' . $this->currency($this->getTotalRebate($vendorId)) . '</a>';
    }

    public function getTotalRebate($vendorId) {
        return $this->currency($this->calculation->sumTotalRebateByVendor($vendorId));
    }

    public function currency($value) {
        return $this->helper->formatToBaseCurrency($value);
    }

    /**
     * Get instance of escaper
     *
     * @return Escaper
     * @deprecated 101.0.7
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
