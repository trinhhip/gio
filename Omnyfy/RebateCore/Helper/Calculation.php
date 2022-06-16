<?php

namespace Omnyfy\RebateCore\Helper;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Omnyfy\RebateCore\Api\Data\IAccumulatedSubtotalRepository;
use Omnyfy\RebateCore\Ui\Form\CalculationBased;
use Omnyfy\RebateCore\Model\ResourceModel\TransactionRebate;
use Omnyfy\RebateCore\Model\ResourceModel\RebateInvoice;
use Omnyfy\RebateCore\Model\ResourceModel\ItemInvoiceRebate;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Model\ThresholdStatusFactory as ThresholdStatusFactory;

/**
 * Class Calculation
 * @package Omnyfy\RebateCore\Helper
 */
class Calculation extends AbstractHelper
{
    /**
     * @var IAccumulatedSubtotalRepository
     */
    protected $accumulatedSubtotalRepository;

    /**
     * @var TransactionRebate
     */
    protected $itemInvoiceRebateCalculate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var ThresholdStatusCollection
     */
    protected $thresholdStatusFactory;

    /**
     * @var RebateInvoice
     */
    protected $rebateInvoice;

    /**
     * @var ItemInvoiceRebate
     */
    protected $itemInvoiceRebate;

    /**
     * Calculation constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        TransactionRebate $itemInvoiceRebateCalculate,
        ThresholdStatusFactory $thresholdStatusFactory,
        RebateInvoice $rebateInvoice,
        ItemInvoiceRebate $itemInvoiceRebate,
        IAccumulatedSubtotalRepository $accumulatedSubtotalRepository
    )
    {
        $this->accumulatedSubtotalRepository = $accumulatedSubtotalRepository;
        $this->itemInvoiceRebateCalculate = $itemInvoiceRebateCalculate;
        $this->date = $date;
        $this->thresholdStatusFactory = $thresholdStatusFactory;
        $this->rebateInvoice = $rebateInvoice;
        $this->itemInvoiceRebate = $itemInvoiceRebate;
        parent::__construct($context);
    }

    /**
     * @return integer
     */
    public function getPayRebate($rebate, $total, $accumulatedSubtotal = null, $orderId)
    {
        if ($rebate->getLockCalculationBasedOn() == CalculationBased::TOTAL_ORDER_VALUE_ABOVE_THRESHOLD) {
            if ($rebate->getLockPaymentFrequency() == PaymentFrequency::PER_ORDER_SETTLEMENT) {
                if ($total > $rebate->getLockThresholdValue()) {
                    $payRebate = $rebate->getLockedRebatePercentage() / 100 * ($total - $rebate->getLockThresholdValue());
                    return $payRebate;
                }
                return 0;
            }
            $thresholdStatus = $this->thresholdStatusFactory->create()->getCollection()->addFieldToFilter('rebate_vendor_id', ['eq' => $rebate->getId()])
            ->addFieldToFilter('order_accumulcation_id', ['eq' => $accumulatedSubtotal->getId()])
            ->getFirstItem();
            if (!$thresholdStatus->getId()) {
                if ($accumulatedSubtotal->getOrderTotalAmount() > $rebate->getLockThresholdValue()) {
                    $thresholdStatus = $this->thresholdStatusFactory->create();
                    $thresholdStatus->setData('order_accumulcation_id', $accumulatedSubtotal->getId());
                    $thresholdStatus->setData('throushold_value',$rebate->getLockThresholdValue());
                    $thresholdStatus->setData('rebate_vendor_id',$rebate->getId());
                    $thresholdStatus->setData('order_id',$orderId);
                    $thresholdStatus->setData('total_amount_trigger', $accumulatedSubtotal->getOrderTotalAmount());
                    $thresholdStatus->save();
                    $payRebate = $rebate->getLockedRebatePercentage() / 100 * ($accumulatedSubtotal->getOrderTotalAmount() - $rebate->getLockThresholdValue());
                    return $payRebate;
                }
                return 0;
            }
        }
        $payRebate = $rebate->getLockedRebatePercentage() / 100 * $total;
        return $payRebate;
    }

    /**
     * @return integer
     */
    public function setAccumulatedSubtotalByVendor($total, $vendorId, $rebate)
    {
        $date = $this->date->gmtDate();
        $accumulatedSubtotalRepository = $this->accumulatedSubtotalRepository->getAccumulatedSubtotalByVendorAndDate($vendorId, $rebate->getId(), $date);
        $accumulatedSubtotal = $accumulatedSubtotalRepository->getOrderTotalAmount() + $total;
        if (!$accumulatedSubtotalRepository->getId()) {
            $accumulatedSubtotalRepository->setVendorId($vendorId);
            $accumulatedSubtotalRepository->setRebateVendorId($rebate->getId());
            if ($rebate->getLockPaymentFrequency() == PaymentFrequency::ANNUALLY_ON_SPECIFIC_DATE) {
                $year = date('Y', strtotime($date));
                $endDate = date($year.'-m-d', strtotime($rebate->getLockEndDate()));
                $startDate = date($year.'-m-d', strtotime($rebate->getLockStartDate()));
                if (strtotime($endDate) < strtotime($date)) {
                    $beforeAt = date('Y-m-d 00:00:01', strtotime($startDate));
                    $afterAt = date('Y-m-d 23:59:59', strtotime($endDate . " +1 year"));
                }else{
                    $beforeAt = date('Y-m-d 00:00:01', strtotime($startDate . " -1 year"));
                    $afterAt = date('Y-m-d 23:59:59', strtotime($endDate));
                }
            }
            if ($rebate->getLockPaymentFrequency() == PaymentFrequency::MONTHLY_AT_END_OF_MONTH) {
                $beforeAt = date('Y-m-01 00:00:01', strtotime($date));
                $afterAt = date("Y-m-t 23:59:59", strtotime($date));
            }
            $accumulatedSubtotalRepository->setStartDate($beforeAt);
            $accumulatedSubtotalRepository->setPayoutDate($afterAt);
        }
        $accumulatedSubtotalRepository->setOrderTotalAmount($accumulatedSubtotal);
        $accumulatedSubtotalRepository->save();
        return $accumulatedSubtotalRepository->getOrderTotalAmount();
    }

    /**
     * @return integer
     */
    public function getAccumulatedSubtotalByVendorAndDate($vendorId, $rebateVendorId)
    {
        $date = $this->date->gmtDate();
        $accumulatedSubtotalRepository = $this->accumulatedSubtotalRepository->getAccumulatedSubtotalByVendorAndDate($vendorId, $rebateVendorId, $date);
        return $accumulatedSubtotalRepository;
    }

    /**
     * @return integer
     */
    public function getPayTaxRebate($rebate, $rebatePayAmount)
    {
        return $rebatePayAmount - ($rebatePayAmount / (1 + $rebate->getLockTaxAmount() / 100));
    }

    /**
     * @param $rebateId
     * @return int
     */
    public function sumTotalRebateByRebateVendor($rebateId)
    {
        return $this->itemInvoiceRebateCalculate->sumTotalRebateByRebateVendor($rebateId);
    }

    /**
     * @param $rebateId
     * @return int
     */
    public function getSumTotalRebatePaidByRebateVendor($rebate)
    {
        if ($rebate->getLockPaymentFrequency() == PaymentFrequency::PER_ORDER_SETTLEMENT) {
            return $this->itemInvoiceRebateCalculate->getSumTotalRebatePaidByRebateVendor($rebate->getId());
        }else{
            return $this->itemInvoiceRebate->getSumTotalRebatePaidByRebateVendor($rebate->getId());
        }
    }

    /**
     * @param $rebateId
     * @return int
     */
    public function getSumNetRebatePaidByRebateVendor($rebate)
    {
        if ($rebate->getLockPaymentFrequency() == PaymentFrequency::PER_ORDER_SETTLEMENT) {
            return $this->itemInvoiceRebateCalculate->getSumNetRebatePaidByRebateVendor($rebate->getId());
        }else{
            return $this->itemInvoiceRebate->getSumNetRebatePaidByRebateVendor($rebate->getId());
        }
    }

    /**
     * @param $rebateId
     * @return int
     */
    public function getSumTaxRebatePaidByRebateVendor($rebate)
    {
        if ($rebate->getLockPaymentFrequency() == PaymentFrequency::PER_ORDER_SETTLEMENT) {
            return $this->itemInvoiceRebateCalculate->getSumTaxRebatePaidByRebateVendor($rebate->getId());
        }else{
            return $this->itemInvoiceRebate->getSumTaxRebatePaidByRebateVendor($rebate->getId());
        }
    }

    /**
     * @param $vendorId
     * @return int
     */
    public function sumTotalRebateByVendor($vendorId)
    {
        return $this->itemInvoiceRebateCalculate->sumTotalRebateByVendor($vendorId);
    }

    /**
     * @param $vendorId
     * @param $orderId
     * @return int
     */
    public function sumTotalRebateByVendorAndOrder($vendorId, $orderId)
    {
        return $this->itemInvoiceRebateCalculate->sumTotalRebateByVendorAndOrder($vendorId, $orderId);
    }

    /**
     * @param $vendorId
     * @param int $status
     * @return int
     */
    public function sumTotalReadyRebateByVendor($vendorId, $status = 1)
    {
        return $this->itemInvoiceRebateCalculate->sumTotalReadyRebateByVendor($vendorId, $status);
    }

    /**
     * @param $vendorId
     * @param int $status
     * @return int
     */
    public function sumTotalPayoutOrderRebateByVendor($vendorId, $status = 1)
    {
        return $this->itemInvoiceRebateCalculate->sumTotalPayoutOrderRebateByVendor($vendorId, $status);
    }

    /**
     * @param $vendorId
     * @param int $status
     * @return int
     */
    public function sumTotalPendingOrderRebateByVendor($vendorId, $status = 1)
    {
        return $this->itemInvoiceRebateCalculate->sumTotalPendingOrderRebateByVendor($vendorId, $status);
    }

    /**
     * @param $rebateId
     * @return int
     */
    public function sumNetRebateByRebateVendor($rebateId)
    {
        return $this->itemInvoiceRebateCalculate->sumNetRebateByRebateVendor($rebateId);
    }

    /**
     * @param $rebateId
     * @return int
     */
    public function sumTaxRebateByRebateVendor($rebateId)
    {
        return $this->itemInvoiceRebateCalculate->sumTaxRebateByRebateVendor($rebateId);
    }

    /**
     * @param $rebateId
     * @param $rebateVendorInvoiceId
     * @return int
     */
    public function sumTotalRebateVendorAndInvoice($rebateId, $rebateVendorInvoiceId)
    {
        return $this->itemInvoiceRebateCalculate->sumTotalRebateVendorAndInvoice($rebateId, $rebateVendorInvoiceId);
    }

    /**
     * @param $rebateId
     * @param $rebateVendorInvoiceId
     * @return int
     */
    public function sumTotalRebatePaidSettlement($vendorId)
    {
        return $this->itemInvoiceRebateCalculate->sumTotalRebatePaidSettlement($vendorId);
    }

    /**
     * @param $rebateId
     * @return int
     */
    public function sumTotalRebateInvoicePaid($vendorId)
    {
        return $this->rebateInvoice->sumTotalRebatePaid($vendorId);
    }

    public function sumTotalRebatePaidByVendor($vendorId){
        return $this->sumTotalReadyRebateByVendor($vendorId);
    }

    /**
     * @param $rebateId
     * @return int
     */
    public function sumTotalRebateMonthPending($vendorId)
    {
        return $this->rebateInvoice->sumTotalRebateMonthPending($vendorId);
    }
    
    /**
     * @param $rebateId
     * @return int
     */
    public function sumTotalRebateAnnualPending($vendorId)
    {
        return $this->rebateInvoice->sumTotalRebateAnnualPending($vendorId);
    }
    
}
