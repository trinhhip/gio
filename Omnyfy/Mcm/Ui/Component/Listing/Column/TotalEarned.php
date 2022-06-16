<?php
namespace Omnyfy\Mcm\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;
use Omnyfy\Mcm\Helper\Data as HelperData;


class TotalEarned extends Column {
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /** Url Path */
    // const PRODUCT_URL_PATH_EDIT = 'catalog/product/edit';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = array(),
        UrlInterface $urlBuilder,
        VendorPayout $vendorPayoutResource, 
        \Magento\Framework\Pricing\Helper\Data $pricing, 
        HelperData $helper,
        array $data = array()) 
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_urlBuilder = $urlBuilder;
        $this->vendorPayoutResource = $vendorPayoutResource;
        $this->pricing = $pricing;
        $this->_helper = $helper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $vendorId = $item["entity_id"];
                $totalEarning = $this->vendorPayoutResource->getTotalEarning($vendorId);
                $href = $this->_urlBuilder->getUrl(
                        'omnyfy_mcm/vendorEarning/index', ['vendor_id' => $vendorId]
                );
                $totalEarned = $totalEarning['total_balance_owing'] - $totalEarning['total_vendor_rebate'];

                $item['total_earned'] = html_entity_decode('<a href="'.$href.'">'.$this->currency($totalEarned).'</a>');
            }
        } 
        return $dataSource;
    }

    public function currency($value) {
        return $this->_helper->formatToBaseCurrency($value);
    }
}

