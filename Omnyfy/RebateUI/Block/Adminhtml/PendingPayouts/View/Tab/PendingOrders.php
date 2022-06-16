<?php

namespace Omnyfy\RebateUI\Block\Adminhtml\PendingPayouts\View\Tab;

use Omnyfy\Mcm\Model\VendorPayoutFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;
use Magento\Framework\Pricing\Helper\Data;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\RebateCore\Helper\Calculation;
use Omnyfy\RebateCore\Helper\Data as HelperCoreRebate;
use Magento\Framework\UrlInterface;

class PendingOrders extends \Omnyfy\Mcm\Block\Adminhtml\PendingPayouts\View\Tab\PendingOrders {

    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Omnyfy_RebateUI::pending_payouts/view/tab/info.phtml';

    protected $calculation;

    protected $helperCoreRebate;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Url path
     */
    const URL_PATH_VENDOR_REPORT = 'rebate_ui/vendorRebateReport/index';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        VendorPayoutFactory $vendorPayoutFactory, 
        VendorPayout $vendorPayoutResource,
        Calculation $calculation,
        HelperCoreRebate $helperCoreRebate,
        Data $pricing, 
        HelperData $helper, 
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $vendorPayoutFactory, $vendorPayoutResource, $pricing, $helper, $data);
        $this->calculation = $calculation;
        $this->urlBuilder = $urlBuilder;
        $this->helperCoreRebate = $helperCoreRebate;
    }

    public function isEnable() {
        return $this->helperCoreRebate->isEnable();
    }

    public function getVendorId(){
        $vendorId = $this->getRequest()->getParam('vendor_id');
        return $vendorId;
    }

    public function getTotalPendingPayoutOrder() {
        return $this->vendorPayoutResource->getTotalPendingPayoutOrder($this->getVendorId());
    }

    public function getTotalRebate() {
        return $this->currency($this->calculation->sumTotalRebateByVendor($this->getVendorId()));
    }

    public function getTotalReadyRebate() {
        return $this->currency($this->calculation->sumTotalPendingOrderRebateByVendor($this->getVendorId()));
    }

    public function getUrlViewReportRebate(){
        return $this->urlBuilder->getUrl(static::URL_PATH_VENDOR_REPORT,['vendor_id' => $this->getVendorId()]);
    }
}
