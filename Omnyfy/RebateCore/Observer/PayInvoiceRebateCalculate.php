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

/**
 * Class PayInvoiceRebateCalculate
 * @package Omnyfy\RebateCore\Observer
 */
class PayInvoiceRebateCalculate implements \Magento\Framework\Event\ObserverInterface
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
     * PayInvoiceRebateCalculate constructor.
     * @param IInvoiceRebateCalculateRepository $rebateInvoiceRepository
     * @param ManagerInterface $messageManager
     * @param CalculationHelper $calculationHelper
     * @param IVendorRebateRepository $vendorRebateRepository
     */
    public function __construct(
        IInvoiceRebateCalculateRepository $rebateInvoiceRepository,
        ManagerInterface $messageManager,
        CalculationHelper $calculationHelper,
        Data $helper,
        PaymentFrequency $paymentFrequency,
        IVendorRebateRepository $vendorRebateRepository
    )
    {
        $this->rebateInvoiceRepository = $rebateInvoiceRepository;
        $this->messageManager = $messageManager;
        $this->vendorRebateRepository = $vendorRebateRepository;
        $this->paymentFrequency = $paymentFrequency;
        $this->calculationHelper = $calculationHelper;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            try {
                $invoice = $observer->getEvent()->getInvoice();
                $order = $invoice->getOrder();
                $itemInserts = [];
                $subTotalByVendor = $this->getSubtotalByVendor($order);
                $this->setAccumulatedSubtotalByVendor($subTotalByVendor);
                foreach ($subTotalByVendor as $vendorId => $subTotal) {
                    $rebates = $this->vendorRebateRepository->getRebateByVendorActiveAndEnable($vendorId);
                    $accumulatedSubtotal = $subTotal;
                    foreach ($rebates as $rebate) {
                        $rebateVendorId = $rebate->getId();
                        if ($rebate->getLockCalculationBasedOn() == CalculationBased::TOTAL_ORDER_VALUE_ABOVE_THRESHOLD) {
                            $accumulatedSubtotal = $this->calculationHelper->getAccumulatedSubtotalByVendorAndDate($vendorId, $rebateVendorId);
                        }
                        $totalPayRebate = $this->calculationHelper->getPayRebate($rebate, $subTotal, $accumulatedSubtotal, $order->getId());
                        $taxPayRebate = $this->calculationHelper->getPayTaxRebate($rebate, $totalPayRebate);
                        $payRebate = $totalPayRebate - $taxPayRebate;
                        $status = ($rebate->getLockPaymentFrequency() == PaymentFrequency::PER_ORDER_SETTLEMENT) ? $this::READY_PAYOUT : 0;
                        if (!isset($itemInserts[$vendorId][$rebateVendorId])) {
                            $itemInserts[$vendorId][$rebateVendorId] = [
                                                                            "rebate_total_amount" => $totalPayRebate,
                                                                            "rebate_net_amount" => $payRebate,
                                                                            "rebate_tax_amount" => $taxPayRebate,
                                                                            "vendor_rebate_id" => $rebateVendorId,
                                                                            "vendor_id" => $vendorId,
                                                                            "status" => $status,
                                                                            "order_id" => $order->getId(),
                                                                            "rebate_percentage" => $rebate->getLockedRebatePercentage(),
                                                                            "payment_frequency" => $rebate->getLockPaymentFrequency()
                                                                        ];
                        }
                    }
                }
                $customerEmail = $order->getCustomerEmail();
                $dataInvoiceRebateCalculate = [
                    "invoice_id" => $invoice->getId(),
                    "order_increment_id" => $order->getIncrementId(),
                    "order_id" => $order->getId(),
                    "order_date" => $order->getCreatedAt(),
                    "customer_email" => $customerEmail
                ];
                if (!empty($itemInserts)) {
                    foreach ($itemInserts as $key => $itemInsert) {
                        $rebateInvoiceRepository = $this->rebateInvoiceRepository->getInvoiceRebateCalculate();
                        $rebateInvoiceRepository->setData($dataInvoiceRebateCalculate);
                        $rebateInvoiceRepository->setData("vendor_id", $key);
                        $rebateInvoiceRepository = $this->rebateInvoiceRepository->saveInvoiceRebateCalculate($rebateInvoiceRepository);
                        $this->rebateInvoiceRepository->insertValues($rebateInvoiceRepository->getId(), $itemInsert);
                    }
                }
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
    }

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
            $totalItem = $item->getRowTotal();
            $total += $totalItem;
        }
        return $total;
    }

    public function setAccumulatedSubtotalByVendor($subTotalByVendor){
        foreach ($subTotalByVendor as $vendorId => $subTotal) {
            $rebates = $this->vendorRebateRepository->getRebateByVendorActiveAndEnable($vendorId);
            foreach ($rebates as $rebate) {
                $rebateVendorId = $rebate->getId();
                if ($rebate->getLockCalculationBasedOn() == CalculationBased::TOTAL_ORDER_VALUE_ABOVE_THRESHOLD) {
                    if ( $rebate->getLockPaymentFrequency() == PaymentFrequency::ANNUALLY_ON_SPECIFIC_DATE || $rebate->getLockPaymentFrequency() == PaymentFrequency::MONTHLY_AT_END_OF_MONTH) {
                        $accumulatedSubtotal = $this->calculationHelper->setAccumulatedSubtotalByVendor($subTotal, $vendorId, $rebate);
                    }
                }
            }
        }
    }

}
 