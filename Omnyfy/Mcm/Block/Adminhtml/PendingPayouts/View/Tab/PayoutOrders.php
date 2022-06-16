<?php
namespace Omnyfy\Mcm\Block\Adminhtml\PendingPayouts\View\Tab;
use Omnyfy\Mcm\Model\VendorPayoutFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout;
use Magento\Framework\Pricing\Helper\Data;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;
use Omnyfy\RebateCore\Helper\Data as HelperCoreRebate;
use Magento\Framework\UrlInterface;
use Omnyfy\RebateCore\Helper\Calculation;

class PayoutOrders extends \Magento\Backend\Block\Widget {

    protected $vendorPayoutFactory;
    protected $vendorPayoutResource;
    protected $pricing;
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'pending_payouts/view/tab/orders_included/info.phtml';

    const URL_PATH_VENDOR_REPORT = 'rebate_ui/vendorRebateReport/index';

    protected $helperCoreRebate;

    protected $urlBuilder;

    protected $calculation;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        VendorPayoutFactory $vendorPayoutFactory,
        VendorPayout $vendorPayoutResource,
        Data $pricing,
        HelperData $helper,
        VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Mcm\Model\Config $config,
        HelperCoreRebate $helperCoreRebate,
        UrlInterface $urlBuilder,
        Calculation $calculation,
        array $data = []
    ) {
        $this->vendorPayoutFactory = $vendorPayoutFactory->create();
        $this->vendorPayoutResource = $vendorPayoutResource;
        $this->pricing = $pricing;
        $this->_helper = $helper;
        $this->vendorRepository = $vendorRepository;
        $this->_config = $config;
        $this->helperCoreRebate = $helperCoreRebate;
        $this->urlBuilder = $urlBuilder;
        $this->calculation = $calculation;
        parent::__construct($context, $data);
    }

    public function getTotalReadyToPay() {
        $vendorId = $this->getRequest()->getParam('vendor_id');
        return $this->vendorPayoutResource->getTotalReadyToPay($vendorId);
    }
    
    public function getTotalPayoutAmount()
    {
        $vendorId = $this->getRequest()->getParam('vendor_id');
        $total = $this->vendorPayoutResource->getOrdersRebateTotal($vendorId, 'orders_included_in_payout');

        if(!empty($total)){
            return $this->currency($total);
        }
        return $this->currency(0);
    }

    public function getTotalFeesCharged(){
        $total = $this->getTotalReadyToPay();
        if(!empty($total)){
            return $this->currency($total['total_fees_paid_incl_tax']);
        }
        return $this->currency(0);
    }

    public function getVendorPayoutBasisType($vendorId){
        $vendor = $this->vendorRepository->getById($vendorId);
        return $vendor->getPayoutBasisType();
    }

    public function currency($value) {
        return $this->_helper->formatToBaseCurrency($value);
    }

    public function isEnable() {
        return $this->helperCoreRebate->isEnable();
    }

    public function getUrlViewReportRebate(){
        return $this->urlBuilder->getUrl(static::URL_PATH_VENDOR_REPORT,['vendor_id' => $this->getVendorId()]);
    }

    public function getTotalReadyRebate() {
        return $this->currency($this->calculation->sumTotalPayoutOrderRebateByVendor($this->getVendorId()));
    }

    public function getVendorId(){
        return $this->getRequest()->getParam('vendor_id');
    }

}
