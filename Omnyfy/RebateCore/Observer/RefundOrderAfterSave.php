<?php

namespace Omnyfy\RebateCore\Observer;

use Magento\Framework\Exception\LocalizedException;
use Omnyfy\RebateCore\Api\Data\IInvoiceRebateCalculateRepository;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Helper\Calculation as CalculationHelper;
use Omnyfy\RebateCore\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Omnyfy\RebateCore\Ui\Form\CalculationBased;
use Magento\Framework\Registry;
use Omnyfy\RebateCore\Model\Repository\TransactionRebateRepository;
use Omnyfy\RebateCore\Model\ThresholdStatusFactory as ThresholdStatusFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorOrder;
use Omnyfy\RebateCore\Api\Data\IAccumulatedSubtotalRepository;

/**
 * Class RefundOrderAfterSave
 * @package Omnyfy\RebateCore\Observer
 */
class RefundOrderAfterSave implements \Magento\Framework\Event\ObserverInterface
{
    /**
     *
     */
    const READY_PAYOUT = 1;

    /**
     * @var IInvoiceRebateCalculateRepository
     */
    protected $rebateInvoiceRepository;

    /**
     * @var IVendorRebateRepository
     */
    protected $vendorRebateRepository;

    /**
     * @var CalculationHelper
     */
    protected $calculationHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var PaymentFrequency
     */
    protected $paymentFrequency;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var TransactionRebateRepository
     */
    protected $transactionRebateRepository;

    /**
     * @var ThresholdStatusCollection
     */
    protected $thresholdStatusFactory;

    /**
     * @var IAccumulatedSubtotalRepository
     */
    protected $accumulatedSubtotalRepository;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * PayInvoiceRebateCalculate constructor.
     * @param IInvoiceRebateCalculateRepository $rebateInvoiceRepository
     * @param ManagerInterface $messageManager
     * @param CalculationHelper $calculationHelper
     * @param IVendorRebateRepository $vendorRebateRepository
     */
    public function __construct(
        IInvoiceRebateCalculateRepository $rebateInvoiceRepository,
        TransactionRebateRepository $transactionRebateRepository,
        ManagerInterface $messageManager,
        CalculationHelper $calculationHelper,
        Data $helper,
        PaymentFrequency $paymentFrequency,
        IVendorRebateRepository $vendorRebateRepository,
        ThresholdStatusFactory $thresholdStatusFactory,
        IAccumulatedSubtotalRepository $accumulatedSubtotalRepository,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Registry $registry
    )
    {
        $this->rebateInvoiceRepository = $rebateInvoiceRepository;
        $this->messageManager = $messageManager;
        $this->vendorRebateRepository = $vendorRebateRepository;
        $this->paymentFrequency = $paymentFrequency;
        $this->calculationHelper = $calculationHelper;
        $this->helper = $helper;
        $this->registry = $registry;
        $this->transactionRebateRepository = $transactionRebateRepository;
        $this->thresholdStatusFactory = $thresholdStatusFactory;
        $this->accumulatedSubtotalRepository = $accumulatedSubtotalRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->helper->isEnable()) {
                $creditmemo = $this->isAfterRefund();
                if ($creditmemo) {
                    $order = $observer->getEvent()->getOrder();
                    $creditmemoVendor = $this->getVendorRefund($creditmemo);
                    $subTotals = $this->getSubtotalByVendor($order);
                    $this->updateAllVendorRebateInOrder($creditmemoVendor, $subTotals, $order->getId());
                }
            }
        } catch (Exception $e) {
            
        }
    }

    /**
     * @param $creditmemoVendor
     * @param $subTotals
     * @param $orderId
     */
    public function updateAllVendorRebateInOrder($creditmemoVendor, $subTotals, $orderId){
        foreach ($creditmemoVendor as $vendorId => $creditmemoVendorTotal) {
            if (isset($subTotals[$vendorId])) {
                $oldTransactions = $this->transactionRebateRepository->getRebateByVendorAndOrderRefund($vendorId, $orderId);
                $this->updateRebateByvendor($oldTransactions, $subTotals[$vendorId], $orderId, $vendorId, $creditmemoVendorTotal);
            }
        }
    }

    /**
     * @param $oldTransactions
     * @param $subTotal
     * @param $orderId
     * @param $vendorId
     * @param $creditmemoVendorTotal
     */
    public function updateRebateByvendor($oldTransactions, $subTotal, $orderId, $vendorId, $creditmemoVendorTotal){
        foreach ($oldTransactions as $oldTransaction) {
            $rebate = $this->vendorRebateRepository->getRebateVendor($oldTransaction->getVendorRebateId());
            $percentage = $oldTransaction->getRebatePercentage();
            if ($rebate->getLockCalculationBasedOn() != CalculationBased::TOTAL_ORDER_VALUE_ABOVE_THRESHOLD) {
                $totalPayRebate = $this->totalRebateBase($percentage, $subTotal);
            }else{
                $totalPayRebate = $this->getTotalPay($subTotal, $rebate, $percentage, $orderId, $oldTransaction, $vendorId, $creditmemoVendorTotal);
                $accumulatedSubtotalRepository = $this->getAccumulatedSubtotalByVendorAndDate($vendorId, $orderId, $rebate);
                if (!empty($accumulatedSubtotalRepository->getId())) {
                    $accumulatedSubtotalRepository->setOrderTotalAmount($accumulatedSubtotalRepository->getOrderTotalAmount() - $creditmemoVendorTotal);
                    $accumulatedSubtotalRepository->save();
                }
            }
            $this->updateTransactionRebate($oldTransaction, $rebate, $totalPayRebate);
        }
    }

    /**
     * @param $vendorId
     * @param $orderId
     * @param $rebate
     * @return mixed
     */
    public function getAccumulatedSubtotalByVendorAndDate($vendorId, $orderId, $rebate){
        $rebateInvoiceRepository = $this->rebateInvoiceRepository->getInvoiceRebateCalculate()->getCollection()->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('main_table.order_id', ['eq' => $orderId])
            ->getFirstItem();
        $createAtRebate = $rebateInvoiceRepository->getCreatedAt();

        $accumulatedSubtotalRepository = $this->accumulatedSubtotalRepository->getAccumulatedSubtotalByVendorAndDate($vendorId, $rebate->getId(), $createAtRebate);
        return $accumulatedSubtotalRepository;
    }

    /**
     * @param $total
     * @param $rebate
     * @param $percentage
     * @param $orderId
     * @param $oldTransaction
     * @param $vendorId
     * @param $totalRefund
     * @return float|int
     */
    public function getTotalPay($total, $rebate, $percentage, $orderId, $oldTransaction, $vendorId, $totalRefund){

        // rebate order settlement
        if ($rebate->getLockPaymentFrequency() == PaymentFrequency::PER_ORDER_SETTLEMENT) {
            if ($total > $rebate->getLockThresholdValue()) {
                $payRebate = $this->totalRebateTriggerThreshold($percentage, $total, $rebate->getLockThresholdValue());
                return $payRebate;
            }
            return 0;
        }
        // if rebate after trigger threshold status
        $thresholdStatus = $this->thresholdStatusFactory->create()->getCollection()->addFieldToFilter('rebate_vendor_id', ['eq' => $rebate->getId()])
            ->addFieldToFilter('order_id', ['eq' => $orderId])
            ->getFirstItem();
        if (empty($thresholdStatus->getId())) {
            if ($oldTransaction->getRebateTotalAmount() > 0) {
                $payRebate = $this->totalRebateBase($percentage, $total);
                return $payRebate;
            }
        }
        //if rebate is not after trigger threshold status
        // Determine rebate calculation at what time period

        $accumulatedSubtotalRepository = $this->getAccumulatedSubtotalByVendorAndDate($vendorId, $orderId, $rebate);
        if (!empty($accumulatedSubtotalRepository)) {
            $thresholdStatus = $this->thresholdStatusFactory->create()->getCollection()->addFieldToFilter('rebate_vendor_id', ['eq' => $rebate->getId()])
                ->addFieldToFilter('order_accumulcation_id', ['eq' => $accumulatedSubtotalRepository->getId()])
                ->getFirstItem();
            //if rebate never crossed the threshold needn't to recalculate
            if (empty($thresholdStatus->getId())) {
                return 0;
            }
            // isset threshold
            $oldTotalAmountTrigger = $thresholdStatus->getTotalAmountTrigger();
            $oldOrderIdTrigger = $thresholdStatus->getOrderId();
            $totalAmountTriggerAfterRefund = $oldTotalAmountTrigger - $totalRefund;
            // order total trigger more than after refund
            $oldTransactionsUpdate = $this->getRebateUpdateByVendorAndOrderRefund($vendorId, $oldOrderIdTrigger, $rebate);
            $orderTrigger = $this->orderRepository->get($oldOrderIdTrigger);
            if ($totalAmountTriggerAfterRefund > $thresholdStatus->getThrousholdValue()) {
                $payRebate = $this->totalRebateTriggerThreshold($rebate->getLockedRebatePercentage(), $totalAmountTriggerAfterRefund, $rebate->getLockThresholdValue());
                $this->updateTransactionRebate($oldTransactionsUpdate, $rebate, $payRebate);
                $this->updateThresholdStatus($thresholdStatus, $totalAmountTriggerAfterRefund);
                return $payRebate;
            }
            $this->updateTransactionRebate($oldTransactionsUpdate, $rebate, 0);
            // order total trigger less after refun calculation by after orders
            // get order after trigger
            $triggerRebateVendor = $this->updateAllRebateBeforeTrigger($vendorId, $thresholdStatus, $accumulatedSubtotalRepository, $totalAmountTriggerAfterRefund, $rebate);
            if ($triggerRebateVendor) {
                return 0;
            }
            $thresholdStatus->delete();
        }

        return 0;
    }

    /**
     * @param $vendorId
     * @param $thresholdStatus
     * @param $accumulatedSubtotalRepository
     * @param $totalAmountTriggerAfterRefund
     * @param $rebate
     * @return bool
     */
    public function updateAllRebateBeforeTrigger($vendorId, $thresholdStatus, $accumulatedSubtotalRepository, $totalAmountTriggerAfterRefund, $rebate){
        $rebateInvoiceRepositoryAfterTriggers = $this->rebateInvoiceRepository->getInvoiceRebateCalculate()->getCollection()->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId])
            ->addFieldToFilter('main_table.created_at', ['gt' =>$thresholdStatus->getDatetimeExceedingThreshold()])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $accumulatedSubtotalRepository->getPayoutDate()]);
        $newTotalAmountTrigger = $totalAmountTriggerAfterRefund;
        foreach ($rebateInvoiceRepositoryAfterTriggers as $rebateInvoiceRepositoryAfterTrigger) {
            $newOrderIdTrigger = $rebateInvoiceRepositoryAfterTrigger->getOrderId();
            $orderTrigger = $this->orderRepository->get($newOrderIdTrigger);
            //update transaction after
            $totalOrderVendor = $this->getTotalOrderByVendorId($orderTrigger, $vendorId);
            $newTotalAmountTrigger += $totalOrderVendor;
            $oldTransactionsUpdate = $this->getRebateUpdateByVendorAndOrderRefund($vendorId, $newOrderIdTrigger, $rebate);
            if ($newTotalAmountTrigger > $thresholdStatus->getThrousholdValue()) {
                $payRebate = $this->totalRebateTriggerThreshold($rebate->getLockedRebatePercentage(), $newTotalAmountTrigger, $rebate->getLockThresholdValue());
                $this->updateTransactionRebate($oldTransactionsUpdate, $rebate, $payRebate);
                $this->updateThresholdStatus($thresholdStatus, $newTotalAmountTrigger, $newOrderIdTrigger);
                return true;
            }
            $this->updateTransactionRebate($oldTransactionsUpdate, $rebate, 0);
        }
        return false;
    }

    /**
     * @param $vendorId
     * @param $orderIdTrigger
     * @param $rebate
     * @return mixed
     */
    public function getRebateUpdateByVendorAndOrderRefund($vendorId, $orderIdTrigger, $rebate){
        return $this->transactionRebateRepository->getRebateUpdateByVendorAndOrderRefund($vendorId, $orderIdTrigger, $rebate);
    }

    /**
     * @param $thresholdStatus
     * @param $newTotalAmountTrigger
     * @param null $newOrderIdTrigger
     */
    public function updateThresholdStatus($thresholdStatus, $newTotalAmountTrigger, $newOrderIdTrigger = NULL){
        $thresholdStatus->setTotalAmountTrigger($newTotalAmountTrigger);
        if ($newOrderIdTrigger) {
            $thresholdStatus->setOrderId($newOrderIdTrigger);
        }
        $thresholdStatus->save();
    }

    /**
     * @param $oldTransaction
     * @param $rebate
     * @param $totalPayRebate
     */
    public function updateTransactionRebate($oldTransaction, $rebate, $totalPayRebate){
        $taxPayRebate = $this->calculationHelper->getPayTaxRebate($rebate, $totalPayRebate);
        $payRebate = $totalPayRebate - $taxPayRebate;
        $oldTransaction->setRebateTotalAmount($totalPayRebate);
        $oldTransaction->setRebateNetAmount($payRebate);
        $oldTransaction->setRebateTaxAmount($taxPayRebate);
        $oldTransaction->save();
    }

    /**
     * @param $order
     * @param $vendorId
     * @return int
     */
    public function getTotalOrderByVendorId($order, $vendorId){
        $totalOrderVendor = 0;
        foreach ($order->getAllItems() as $item) {
            if ($vendorId == $item->getVendorId()) {
                $total = $item->getRowTotal() - $item->getAmountRefunded();
                $totalOrderVendor += $total;
            }
        }
        return $totalOrderVendor;
    }

    /**
     * @return mixed
     */
    public function isAfterRefund(){
        return $this->registry->registry('creditmemo_save_after');
    }

    /**
     * @param $creditmemo
     * @return array
     */
    public function getVendorRefund($creditmemo){
        $items = $creditmemo->getItems();
        $data = [];
        foreach($items as $item){
            $itemOrderCollection = $this->orderItemRepository->get($item->getOrderItemId());
            $vendorId = $itemOrderCollection->getVendorId();
            if (isset($data[$vendorId])) {
                $data[$vendorId] += $this->getTotalRefundItemByVendor($vendorId, $item);
            }else{
                $data[$vendorId] = $this->getTotalRefundItemByVendor($vendorId, $item);
            }
        }
        return $data;
    }

    public function getTotalRefundItemByVendor($vendorId, $item)
    {
        if ($this->helper->isWhosaleVendor($vendorId)) {
            return $item->getQty() * $item->getBaseCost();
        }
        return $item->getRowTotal();
    }

    /**
     * @param $order
     * @return array
     */
    public function getSubtotalByVendor($order){
        $data = [];
        $itemVendorByOrder = $this->itemVendorByOrder($order);
        foreach ($itemVendorByOrder as $vendorId => $listItem) {
            if ($this->helper->isWhosaleVendor($vendorId)) {
                $data[$vendorId] = $this->getTotalByWhosaleVendor($listItem);
            }else {
                $data[$vendorId] = $this->getTotalByCommissionVendor($listItem);
            }
            
        }
        return $data;
    }

    public function itemVendorByOrder($order)
    {
        $itemVendor = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $vendorId = $item->getVendorId();
            $itemVendor[$vendorId][] = $item;
        }
        return $itemVendor;
    }

    public function getTotalByWhosaleVendor($listItem)
    {
        $total = 0;
        foreach ($listItem as $item) {
            if ($item->getProductType() == "configurable") {
                $childItems = $item->getChildrenItems();
                foreach($childItems as $childItem) {
                    $total += ($childItem->getQtyOrdered() - $childItem->getQtyRefunded()) * $childItem->getBaseCost();
                }
            }else{
                $total += ($item->getQtyOrdered() - $item->getQtyRefunded()) * $item->getBaseCost();
            }
        }
        return $total;
    }

    public function getTotalByCommissionVendor($listItem)
    {
        $total = 0;
        foreach ($listItem as $item) {
            $totalItem = $item->getRowTotal()- $item->getAmountRefunded();;
            $total += $totalItem;
        }
        return $total;
    }

    /**
     * @param $percentage
     * @param $total
     * @return float|int
     */
    public function totalRebateBase($percentage, $total){
        $payRebate = $percentage / 100 * $total;
        return $payRebate;
    }

    /**
     * @param $percentage
     * @param $totalAmountTrigger
     * @param $lockThresholdValue
     * @return float|int
     */
    public function totalRebateTriggerThreshold($percentage, $totalAmountTrigger, $lockThresholdValue){
        $payRebate = $percentage / 100 * ($totalAmountTrigger - $lockThresholdValue);
        return $payRebate;
    }

}
 