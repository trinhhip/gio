<?php

namespace Omnyfy\Mcm\Block\Adminhtml\VendorEarnings\Renderer;

use Magento\Framework\DataObject;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;
use Omnyfy\Mcm\Helper\Data as HelperData;

class TotalEarnedColumn extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    public function __construct(\Magento\Framework\UrlInterface $urlBuilder, VendorPayout $vendorPayoutResource, \Magento\Framework\Pricing\Helper\Data $pricing, HelperData $helper
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->vendorPayoutResource = $vendorPayoutResource;
        $this->pricing = $pricing;
        $this->_helper = $helper;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row) {
        $vendorId = $row->getData('entity_id');
        $totalEarning = $this->vendorPayoutResource->getTotalEarning($vendorId);
        $href = $this->_urlBuilder->getUrl(
                'omnyfy_mcm/vendorEarning/index', ['vendor_id' => $vendorId]
        );

        $totalEarnings = $totalEarning['total_balance_owing'] - $totalEarning['total_vendor_rebate'];
        error_log('total earning minus rebate ' . $totalEarnings . ' - ' . $totalEarning['total_vendor_rebate']);

        return '<a href="' . $href . '" title="Total Earned">' . __($this->currency($totalEarnings)) . '</a>';
    }

    public function currency($value) {
        return $this->_helper->formatToBaseCurrency($value);
    }

}

?>