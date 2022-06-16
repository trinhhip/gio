<?php


namespace Omnyfy\Mcm\Observer;


use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Omnyfy\Mcm\Helper\Data;
use Omnyfy\Mcm\Helper\McmVendorOrder;
use Omnyfy\Mcm\Helper\McmVendorOrderHelper;
use Omnyfy\Mcm\Helper\McmVendorOrderItem;
use Omnyfy\Mcm\Model\CategoryCommissionReport;
use Omnyfy\Mcm\Model\ResourceModel\FeesManagement as FeesManagementResource;
use Omnyfy\Mcm\Model\VendorPayout as VendorPayoutModel;
use Omnyfy\Mcm\Model\ResourceModel\VendorShipping\CollectionFactory as VendorShippingCollection;

class OrderUpdateForCreditMemo implements ObserverInterface
{
    /**
     * @var McmVendorOrder
     */
    private $mcmVendorOrder;
    /**
     * @var McmVendorOrderHelper
     */
    private $mcmVendorOrderHelper;
    /**
     * @var McmVendorOrderItem
     */
    private $mcmVendorOrderItem;
    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var CategoryCommissionReport
     */
    private $categoryCommissionReport;
    /**
     * @var FeesManagementResource
     */
    private $feesManagementResource;
    /**
     * @var Data
     */
    private $_helper;
    /**
     * @var VendorPayoutModel
     */
    private $vendorPayoutModel;

    /**
     * @var VendorShippingCollection
     */
    private $vendorShippingCollection;

    /**
     * OrderUpdateForCreditMemo constructor.
     * @param McmVendorOrder $mcmVendorOrder
     * @param McmVendorOrderHelper $mcmVendorOrderHelper
     * @param McmVendorOrderItem $mcmVendorOrderItem
     * @param CollectionFactory $categoryCollectionFactory
     * @param CategoryCommissionReport $categoryCommissionReport
     * @param Data $helper
     * @param FeesManagementResource $feesManagementResource
     * @param VendorPayoutModel $vendorPayoutModel
     * @param VendorShippingCollection $vendorShippingCollection
     */
    public function __construct(
        McmVendorOrder $mcmVendorOrder,
        McmVendorOrderHelper $mcmVendorOrderHelper,
        McmVendorOrderItem $mcmVendorOrderItem,
        CollectionFactory $categoryCollectionFactory,
        CategoryCommissionReport $categoryCommissionReport,
        Data $helper,
        FeesManagementResource $feesManagementResource,
        VendorPayoutModel $vendorPayoutModel,
        VendorShippingCollection $vendorShippingCollection
    )
    {
        $this->mcmVendorOrder = $mcmVendorOrder;
        $this->mcmVendorOrderHelper = $mcmVendorOrderHelper;
        $this->mcmVendorOrderItem = $mcmVendorOrderItem;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryCommissionReport = $categoryCommissionReport;
        $this->_helper = $helper;
        $this->feesManagementResource = $feesManagementResource;
        $this->vendorPayoutModel = $vendorPayoutModel;
        $this->vendorShippingCollection = $vendorShippingCollection;
    }

    public function execute(EventObserver $observer) {
        /* @var $creditmemo Creditmemo */

        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $orderId = $order->getId();
        $items = $order->getAllItems();
        $vendorIds = [];
        $qtyRefunded = $amountRefunded = $baseAmountRefunded = $shippingRefundedVendor = $baseShippingRefundedVendor = [];
        $vendorRefundedShipping = [];
        $shippingRefundedAmount = $order->getShippingRefunded() + $order->getShippingTaxRefunded();
        $baseShippingRefundedAmount = $order->getBaseShippingRefunded() + $order->getBaseShippingTaxRefunded();
        $shippedVendorIds = [];
        foreach ($items as $itemOrder) {
            $item = $itemOrder;
            if ($item->getProductType() == 'configurable'
                || $item->getProductType() == 'mcp_product'
                || $item->getProductType() == 'mvcp') {
                continue;
            }
            /*
             * 2019-08-28 11:11 Jing Xiao
             * FOR issue with bundle product.
             * Assume bundle product only calculate fees on parent item.
             */
            $isChildOfBundle = false;
            if ($item->getParentItem()) {
                $parentItem = $item->getParentItem();
                if ($parentItem->getProductType() != 'mvcp') {
                    $item = $parentItem;
                }
                if ('bundle' == $item->getProductType()) {
                    $isChildOfBundle = true;
                }
            }
            if ($isChildOfBundle) {
                continue;
            }
            $vendorId = $item->getVendorId();
            if(!$vendorId){
                continue;
            }
            if (!in_array($vendorId, $shippedVendorIds) && $item->getQtyShipped() > 0) {
                $shippedVendorIds[] = $vendorId;
            }
            $vendorId = $item->getData('vendor_id');
            if (!isset($amountRefunded[$vendorId])) {
                $amountRefunded[$vendorId] = 0;
            }
            if (!isset($baseAmountRefunded[$vendorId])) {
                $baseAmountRefunded[$vendorId] = 0;
            }
            if (!isset($qtyRefunded[$vendorId])) {
                $qtyRefunded[$vendorId] = 0;
            }
            $itemQty = (int) ($item->getQtyOrdered() - $item->getQtyRefunded());
            $finalItemTotal = ($item->getBasePriceInclTax() * $itemQty) - $item->getBaseDiscountAmount() + $item->getBaseDiscountRefunded();
            if($itemQty == 0){
                $finalItemTotal = 0;
            }
            $categoryCollection = $this->categoryCollectionFactory->create();

            $product = $item->getProduct();
            $categoryIds = $product->getCategoryIds();
            $sellerFeeTax = 0.00;
            if (!empty($categoryIds)) {
                $categories = $categoryCollection
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', $categoryIds);

                $itemCatCommissionPercentage = 0;
                foreach ($categories as $category) {
                    $category_commission = $category->getCategoryCommissionPercentage();
                    $itemCatCommissionPercentage += $category_commission;
                }
            } else {
                $itemCatCommissionPercentage = 0;
            }

            if ($this->_helper->isEnable()) {
                $vendorTaxRate = $this->feesManagementResource->getVendorTaxRateByVendorId($vendorId);
            } else {
                $vendorTaxRate = 0;
            }

            if ($this->_helper->isEnable() && $this->_helper->allowCategoryCommisssion()) {
                $category_commission_fee = ($finalItemTotal * $itemCatCommissionPercentage) / 100;
                if (!empty($vendorTaxRate)) {
                    $categoryFeeTax = ($category_commission_fee * $vendorTaxRate) / 100;
                } else {
                    $categoryFeeTax = 0;
                }
            } else {
                $category_commission_fee = 0;
                $categoryFeeTax = 0;
            }

            if ($this->_helper->isEnable()) {
                $sellerFee = 0.00;
                if ($this->feesManagementResource->isVendorFeeActive($vendorId)) {
                    $sellerFeePercentage = $this->feesManagementResource->getSellerFeeByVendorId($vendorId);
                    $sellerMinFee = (double) $this->feesManagementResource->getSellerMinFeeByVendorId($vendorId);
                    $sellerMaxFee = (double) $this->feesManagementResource->getSellerMaxFeeByVendorId($vendorId);
                    $sellerFee = (double) ($finalItemTotal * $sellerFeePercentage) / 100;

                    if ($sellerMaxFee) {
                        if ($sellerFee > $sellerMaxFee) {
                            $sellerFee = $sellerMaxFee;
                        }
                    }
                    if ($sellerMinFee) {
                        if ($sellerFee < $sellerMinFee) {
                            $sellerFee = $sellerMinFee;
                        }
                    }
                } else {
                    if ($this->_helper->isVendorFeeEnable()) {
                        $sellerFeePercentage = (double) $this->_helper->getDefaultSellerFees();
                        $sellerFee = (double) ($finalItemTotal * $sellerFeePercentage) / 100;
                        $sellerMaxFee = (double) $this->_helper->getDefaultMaxSellerFees();
                        if ($sellerMaxFee) {
                            if ($sellerFee > $sellerMaxFee) {
                                $sellerFee = $sellerMaxFee;
                            }
                        }
                        $sellerMinFee = (double) $this->_helper->getDefaultMinSellerFees();
                        if ($sellerMinFee) {
                            if ($sellerFee < $sellerMinFee) {
                                $sellerFee = $sellerMinFee;
                            }
                        }
                    }
                }
                if (!empty($vendorTaxRate)) {
                    $sellerFeeTax = ($sellerFee * $vendorTaxRate) / 100;
                }
            } else {
                $sellerFee = 0;
                $sellerFeeTax = 0;
            }

            $row_total = $item->getPrice() * $itemQty;
            $tax_amount = $categoryFeeTax + $sellerFeeTax;
            $row_total_incl_tax = $item->getPriceInclTax() * $itemQty;
            $mcmorderItemData = [
                'order_item_id' => $item->getId(),
                'vendor_id' => $vendorId,
                'seller_fee' => $sellerFee,
                'seller_fee_tax' => $sellerFeeTax,
                'category_commission_percentage' => $itemCatCommissionPercentage,
                'category_fee' => $category_commission_fee,
                'category_fee_tax' => $categoryFeeTax,
                'row_total' => $row_total,
                'tax_amount' => $tax_amount,
                'tax_percentage' => $vendorTaxRate,
                'row_total_incl_tax' => $row_total_incl_tax
            ];
            if ($this->_helper->isEnable()) {
                $this->mcmVendorOrderItem->updateMcmOrderItemsRelation($mcmorderItemData);
            }
            $vendorIds[] = $vendorId;


            $amountRefunded[$vendorId] += $item->getAmountRefunded() + $item->getTaxRefunded() - $item->getDiscountRefunded();
            $baseAmountRefunded[$vendorId] += $item->getBaseAmountRefunded() + $item->getBaseTaxRefunded()  - $item->getBaseDiscountRefunded();

            $qtyRefunded[$vendorId] += $item->getQtyRefunded();

        }
        if (empty($vendorIds)) {
            return;
        }
        $vendorIds = array_unique($vendorIds);
        $isRefundFull = $this->checkRefundFull($order);
            foreach ($vendorIds as $vendorId) {
                if ($this->_helper->isEnable()) {
                    $vendorTaxRate = $this->feesManagementResource->getVendorTaxRateByVendorId($vendorId);
                    $vendorItemsTotals = $this->feesManagementResource->getVendorItemsTotals($vendorId, $orderId);
                    $totalCategoryFee = (float)$this->feesManagementResource->getTotalCategoryFee($vendorId, $orderId);
                    $totalSellerFee = (float)$this->feesManagementResource->getTotalSellerFee($vendorId, $orderId);
                    $tax = (float)$this->feesManagementResource->getTotalTaxOnFees($vendorId, $orderId);

                    $disbursementFee = 0;
                    if ($this->feesManagementResource->isVendorFeeActive($vendorId)) {
                        $disbursementFee = $this->feesManagementResource->getDisbursmentFeeByVendorId($vendorId);
                    } else {
                        if ($this->_helper->isVendorFeeEnable()) {
                            $disbursementFee = $this->_helper->getDefaultDisbursementFees();
                        }
                    }
                    $disbursementFeeTax = ($disbursementFee * $vendorTaxRate) / 100;

                    $totalTaxOnFees = round($tax + $disbursementFeeTax, 2);

                    $vendorOrderTotals = $this->vendorPayoutModel->getResource()->getVendorOrderTotals($orderId, $vendorId);
                    $vendorTotal = 0.00;
                    $vendorTotalInclTax = 0.00;
                    $totalCategoryFeeTax = 0.00;
                    $totalSellerFeeTax = 0.00;
                    if (!empty($vendorOrderTotals)) {
                        $vendorTotal = $vendorOrderTotals['row_total'];
                        $vendorTotalInclTax = $vendorOrderTotals['row_total_incl_tax'];
                        $totalCategoryFeeTax = $vendorOrderTotals['category_fee_tax'];
                        $totalSellerFeeTax = $vendorOrderTotals['seller_fee_tax'];
                    }
                    $grandTotal = 0;
                    if (!empty($vendorItemsTotals)) {
                        $grandTotal = $vendorItemsTotals['row_total'] + $vendorItemsTotals['tax_amount'] - $vendorItemsTotals['discount_amount'];
                    }

                    $fullRefundVendor = $vendorItemsTotals['qty_ordered'] == $qtyRefunded[$vendorId];
                    if (!isset($shippingRefundedVendor[$vendorId])) {
                        $shippingRefundedVendor[$vendorId] = 0;
                    }
                    if (!isset($baseShippingRefundedVendor[$vendorId])) {
                        $baseShippingRefundedVendor[$vendorId] = 0;
                    }
                    $isVendorShipped = in_array($vendorId, $shippedVendorIds) ? true : false;
                    /** Process Shipping Refund Amount by vendor */
                    if ($this->isRefundedShipping($isRefundFull, $fullRefundVendor, $qtyRefunded[$vendorId], $isVendorShipped)) {
                        $shippingRefunded = $baseShippingRefunded = $newShippingRefundedAmount = $newBaseShippingRefundedAmount = 0;
                        if ($baseShippingRefundedAmount > 0 && $shippingRefundedAmount > 0) {
                            if (!in_array($vendorId, $vendorRefundedShipping)) {
                                $vendorShippingCollection = $this->vendorShippingCollection->create()
                                    ->addFieldToFilter('vendor_id', $vendorId)
                                    ->addFieldToFilter('order_id', $orderId);
                                if ($vendorShippingCollection->getSize() > 0) {
                                    foreach ($vendorShippingCollection->getItems() as $vendorShipping) {
                                        $shippingRefunded += $vendorShipping->getShippingInclTax();
                                        $baseShippingRefunded += $vendorShipping->getBaseShippingInclTax();
                                    }
                                    $vendorRefundedShipping[] = $vendorId;
                                }
                            }
                            $newShippingRefundedAmount = $shippingRefundedAmount - $shippingRefunded;
                            $newBaseShippingRefundedAmount = $baseShippingRefundedAmount - $baseShippingRefunded;
                        }

                        $shippingRefundedVendor[$vendorId] += $shippingRefundedAmount > $shippingRefunded ? $shippingRefunded : $shippingRefundedAmount;
                        $baseShippingRefundedVendor[$vendorId] += $baseShippingRefundedAmount > $baseShippingRefunded ? $baseShippingRefunded : $baseShippingRefundedAmount;
                        $shippingRefundedAmount = $newShippingRefundedAmount;
                        $baseShippingRefundedAmount = $newBaseShippingRefundedAmount;
                    }

                    $mcmVendorOrder = [
                        'order_id' => $orderId,
                        'order_increment_id' => $order->getIncrementId(),
                        'vendor_id' => $vendorId,
                        'total_category_fee' => $totalCategoryFee,
                        'total_category_fee_tax' => $totalCategoryFeeTax,
                        'total_seller_fee' => $totalSellerFee,
                        'total_seller_fee_tax' => $totalSellerFeeTax,
                        'disbursement_fee' => $disbursementFee,
                        'disbursement_fee_tax' => $disbursementFeeTax,
                        'total_tax_onfees' => $totalTaxOnFees,
                        'vendor_total' => $vendorTotal,
                        'vendor_total_incl_tax' => $vendorTotalInclTax,
                        'subtotal' => $vendorItemsTotals['row_total'],
                        'base_subtotal' => $vendorItemsTotals['base_row_total'],
                        'subtotal_incl_tax' => $vendorItemsTotals['row_total_incl_tax'],
                        'base_subtotal_incl_tax' => $vendorItemsTotals['base_row_total_incl_tax'],
                        'tax_amount' => $vendorItemsTotals['tax_amount'],
                        'base_tax_amount' => $vendorItemsTotals['base_tax_amount'],
                        'discount_amount' => $vendorItemsTotals['discount_amount'],
                        'base_discount_amount' => $vendorItemsTotals['base_discount_amount'],
                        'grand_total' => ($grandTotal - ($amountRefunded[$vendorId] + $shippingRefundedVendor[$vendorId])),
                        'payout_calculated' => 0,
                        'base_grand_total' => ($vendorItemsTotals['base_row_total'] + $vendorItemsTotals['base_tax_amount'] - $vendorItemsTotals['base_discount_amount']) - ($baseAmountRefunded[$vendorId] + $baseShippingRefundedVendor[$vendorId]),
                        'refund_amount' => $amountRefunded[$vendorId] + $shippingRefundedVendor[$vendorId],
                        'base_refund_amount' => $baseAmountRefunded[$vendorId] + $baseShippingRefundedVendor[$vendorId],
                        'refund_shipping_amount' => $shippingRefundedVendor[$vendorId],
                        'base_refund_shipping_amount' => $baseShippingRefundedVendor[$vendorId]
                    ];


                    if($isRefundFull || $fullRefundVendor){
                        $mcmVendorOrder['payout_status'] = 2;
                        $mcmVendorOrder['payout_action'] = 2;
                        $mcmVendorOrder['disbursement_fee'] = 0;
                        $mcmVendorOrder['disbursement_fee_tax'] = 0;
                    }
                    $this->mcmVendorOrder->updateMcmVendorOrderRelation($mcmVendorOrder);
                }
            }

    }

    private function checkRefundFull($order): bool
    {
        /* @var $order Order */
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $orderItem){
            if($orderItem->getQtyRefunded() != $orderItem->getQtyOrdered()){
                return false;
            }
        }
        return true;

    }

    private function isRefundedShipping($isRefundFull, $fullRefundVendor, $qtyRefundedVendor, $isVendorShipped)
    {
        if ($isVendorShipped) {
            return false;
        }
        if ($this->_helper->getGeneralConfig(Data::REFUND_SHIPPING_PARTIAL) && $qtyRefundedVendor > 0) {
            return true;
        }
        if ($this->_helper->getGeneralConfig(Data::REFUND_SHIPPING_FULL)) {
            if($isRefundFull || $fullRefundVendor){
                return true;
            }
        }
        return false;
    }
}


