<?php

namespace Omnyfy\RebateUI\Block\Adminhtml\VendorRebateReport;

use Magento\Backend\Model\UrlInterface;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;
use Omnyfy\RebateCore\Helper\Calculation as CalculationHelper;
use Omnyfy\RebateUI\Helper\Data;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;

class Index extends \Magento\Backend\Block\Widget {


    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'report/index.phtml';

    protected $calculationHelper;

    protected $helper;

    protected $paymentFrequency;

    protected $vendorRebateRepository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        IVendorRebateRepository $vendorRebateRepository,
        Data $helper,
        PaymentFrequency $paymentFrequency,
        CalculationHelper $calculationHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->vendorRebateRepository = $vendorRebateRepository;
        $this->calculationHelper = $calculationHelper;
        $this->paymentFrequency = $paymentFrequency;
        $this->helper = $helper;
    }

    public function getVendorId() {
        $vendorId = '';
        $vendorId = $this->getRequest()->getParam('vendor_id');
        if (!$vendorId) {
            $vendorInfo = $this->_backendSession->getVendorInfo();
            if (!empty($vendorInfo) && isset($vendorInfo['vendor_id'])) {
                $vendorId = $vendorInfo['vendor_id'];
            }
        }
        return $vendorId;
    }

    public function getRebateVendorActive(){
        $vendorId = $this->getVendorId();
        $rebates = $this->vendorRebateRepository->getRebateByVendorActive($vendorId);
        return $rebates;
    }

    public function getSumTotalRebateByRebateVendor($rebateId)
    {
        $total = $this->calculationHelper->sumTotalRebateByRebateVendor($rebateId);
        return  $this->helper->formatToBaseCurrency($total);
    }

    public function getSumNetRebateByRebateVendor($rebateId)
    {
        $total = $this->calculationHelper->sumNetRebateByRebateVendor($rebateId);
        return  $this->helper->formatToBaseCurrency($total);
    }

    public function getSumTaxRebateByRebateVendor($rebateId)
    {
        $total = $this->calculationHelper->sumTaxRebateByRebateVendor($rebateId);
        return  $this->helper->formatToBaseCurrency($total);
    }

    public function listPaymentFrequency(){
        return $this->paymentFrequency->toArray();
    }

    public function getSumTotalRebatePaidByRebateVendor($rebate)
    {
        $total = $this->calculationHelper->getSumTotalRebatePaidByRebateVendor($rebate);
        return  $this->helper->formatToBaseCurrency($total);
    }

    public function getSumNetRebatePaidByRebateVendor($rebate)
    {
        $total = $this->calculationHelper->getSumNetRebatePaidByRebateVendor($rebate);
        return  $this->helper->formatToBaseCurrency($total);
    }

    public function getSumTaxRebatePaidByRebateVendor($rebate)
    {
        $total = $this->calculationHelper->getSumNetRebatePaidByRebateVendor($rebate);
        return  $this->helper->formatToBaseCurrency($total);
    }
}
