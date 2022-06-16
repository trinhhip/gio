<?php

namespace Omnyfy\RebateCore\Observer;

use Magento\Framework\Exception\LocalizedException;
use Omnyfy\RebateCore\Api\Data\IRebateRepository;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;
use Omnyfy\RebateCore\Helper\Data;
use Magento\Backend\Model\Session;

/**
 * Class VendorRebateSave
 * @package Omnyfy\RebateCore\Observer
 */
class VendorRebateSave implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var IRebateRepository
     */
    protected $rebateRepository;

    /**
     * @var IVendorRebateRepository
     */
    protected $rebateVendorRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $_backendSession;

    /**
     * VendorRebateSave constructor.
     * @param IRebateRepository $rebateRepository
     * @param IVendorRebateRepository $rebateVendorRepository
     */
    public function __construct(
        IRebateRepository $rebateRepository,
        Data $helper,
        Session $backendSession,
        IVendorRebateRepository $rebateVendorRepository
    )
    {
        $this->rebateRepository = $rebateRepository;
        $this->rebateVendorRepository = $rebateVendorRepository;
        $this->helper = $helper;
        $this->_backendSession = $backendSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $vendor = $observer->getData('vendor');
            $formData = $observer->getData('form_data');
            $vendorId = $vendor->getId();
            if (!empty($formData['locked_rebate_percentage'])) {
                foreach ($formData['locked_rebate_percentage'] as $rebateId => $rebatePercentage) {
                    if (!$this->rebateVendorRepository->checkRebateByVendorActive($vendorId, $rebateId)) {
                        $contribution = $this->rebateRepository->issetOptionContribution($rebateId, $rebatePercentage);
                        if ($contribution) {
                            $rebate = $this->rebateRepository->getRebate($rebateId);
                            $rebateModel = $this->rebateVendorRepository->getRebateVendor();
                            $rebateModel->setData($this->formatDataRebateVendor($rebate, $vendorId, $contribution));
                            $this->rebateVendorRepository->saveVendorRebate($rebateModel);
                        }
                    }
                }
            }
            if ($this->isRoleMO() && !empty($formData['admin_update_locked_rebate_percentage'])) {
                foreach ($formData['admin_update_locked_rebate_percentage'] as $id => $rebatePercentage) {
                    $rebateModel = $this->rebateVendorRepository->getRebateVendor($id);
                    $rebateModel->setData('locked_rebate_percentage', $rebatePercentage);
                    $this->rebateVendorRepository->saveVendorRebate($rebateModel);
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isRoleMO()
    {
        $vendorInfo = $this->_backendSession->getVendorInfo();
        return empty($vendorInfo);
    }

    /**
     * @param $rebate
     * @param $vendorId
     * @param $contribution
     * @return array
     */
    public function formatDataRebateVendor($rebate, $vendorId, $contribution)
    {
        $data = [
            "vendor_id" => $vendorId,
            "rebate_id" => $rebate->getEntityId(),
            "lock_status" => $rebate->getStatus(),
            "lock_name" => $rebate->getName(),
            "lock_description" => $rebate->getDescription(),
            "lock_payment_frequency" => $rebate->getPaymentFrequency(),
            "lock_calculation_based_on" => $rebate->getCalculationBasedOn(),
            "lock_tax_amount" => $rebate->getTaxAmount(),
            "lock_threshold_value" => $rebate->getThresholdValue(),
            "lock_tax_title" => $rebate->getTaxTitle(),
            "lock_start_date" => $rebate->getStartDate(),
            "lock_end_date" => $rebate->getEndDate(),
            "locked_rebate_percentage" => $contribution['rebate_percentage']
        ];
        return $data;
    }
}
 