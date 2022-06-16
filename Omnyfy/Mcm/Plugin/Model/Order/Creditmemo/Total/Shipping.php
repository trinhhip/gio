<?php
namespace Omnyfy\Mcm\Plugin\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order\Invoice;
use Omnyfy\Mcm\Model\ResourceModel\VendorShipping\CollectionFactory as VendorShippingCollection;
use Omnyfy\Vendor\Helper\Shipping as ShippingHelper;
use Omnyfy\Mcm\Helper\Data as DataHelper;
use Magento\Framework\App\RequestInterface;
use Omnyfy\Mcm\Model\ResourceModel\OrderRefund;
use Magento\Sales\Model\OrderFactory;

class Shipping
{
    protected $vendorShippingCollection;

    protected $shippingHelper;

    protected $dataHelper;

    protected $request;

    protected $orderRefund;

    protected $orderFactory;

    protected $fullRefundedVendorIds = [];

    protected $orderVendorids = [];

    protected $resource;

    protected $messageManager;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    public function __construct(
        VendorShippingCollection $vendorShippingCollection,
        ShippingHelper $shippingHelper,
        DataHelper $dataHelper,
        RequestInterface $request,
        OrderRefund $orderRefund,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        OrderFactory $orderFactory,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->vendorShippingCollection = $vendorShippingCollection;
        $this->shippingHelper = $shippingHelper;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->orderRefund = $orderRefund;
        $this->resource = $resource;
        $this->messageManager = $messageManager;
        $this->orderFactory = $orderFactory;
        $this->taxConfig = $taxConfig;
    }

    /**
     * Deduct the base_shipping_amount to not allow MO to refund shipping amount of vendor who has shipped any items
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Total\Shipping $subject
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return \Magento\Sales\Model\Order\Creditmemo[]
     */
    public function beforeCollect(
        \Magento\Sales\Model\Order\Creditmemo\Total\Shipping $subject,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ) {
        $order = $creditmemo->getOrder();
        $orderId = $order->getId();
        if (!$this->dataHelper->getGeneralConfig(DataHelper::REFUND_SHIPPING_PARTIAL) && !$this->dataHelper->getGeneralConfig(DataHelper::REFUND_SHIPPING_FULL)) {
            return $this->emptyRefundShipping($order, $creditmemo);
        }
        $shippingAmountDeduct = $baseShippingAmountDeduct = $shippingInclTaxDeduct = $baseShippingInclTaxDeduct = 0;
        $vendorDeducted = [];
        
        $vendorRefundedData = $this->orderRefund->getVendorOrderRefundTotals($orderId);
        foreach ($creditmemo->getItems() as $creditmemoItem) {
            $orderItem = $order->getItemById($creditmemoItem->getOrderItemId());
            $vendorId = $orderItem->getVendorId();
            if(isset($vendorRefundedData[$vendorId]['qty_refunded'])) {
                $vendorRefundedData[$vendorId]['qty_refunded'] += $creditmemoItem->getQty();
            }
        }

        if (!$this->dataHelper->getGeneralConfig(DataHelper::REFUND_SHIPPING_PARTIAL) && $this->dataHelper->getGeneralConfig(DataHelper::REFUND_SHIPPING_FULL)) {
            //Not allow to refund shipping if not full refund
            if ($this->shippingHelper->getCalculateShippingBy() == 'overall_cart') {
                $orderRefundedData = $this->orderRefund->getOrderRefundTotals($orderId);
                $newRefundQty = 0;
                foreach ($creditmemo->getItems() as $creditmemoItem) {
                    $newRefundQty += $creditmemoItem->getQty();
                }
                if ($orderRefundedData['qty_refunded'] + $newRefundQty < $orderRefundedData['qty_ordered']) {
                    return $this->emptyRefundShipping($order, $creditmemo);
                }
            } else {
                foreach ($vendorRefundedData as $vendorId => $data) {
                    if ($data['qty_refunded'] < $data['qty_ordered']) {
                        $vendorShippingCollection = $this->vendorShippingCollection->create()
                            ->addFieldToFilter('vendor_id', $vendorId)
                            ->addFieldToFilter('order_id', $orderId);
                        if ($vendorShippingCollection->getSize() > 0) {
                            foreach ($vendorShippingCollection->getItems() as $vendorShipping) {
                                $shippingAmountDeduct += $vendorShipping->getShippingAmount();
                                $baseShippingAmountDeduct += $vendorShipping->getBaseShippingAmount();
                                $shippingInclTaxDeduct += $vendorShipping->getShippingInclTax();
                                $baseShippingInclTaxDeduct += $vendorShipping->getBaseShippingInclTax();
                            }
                            $vendorDeducted[] = $vendorId;
                        }
                    }
                }
            }
        }
        foreach ($order->getItems() as $item) {
            $vendorId = $item->getVendorId();
            if ($item->getQtyShipped() > 0 || (isset($vendorRefundedData[$vendorId]['qty_refunded']) && $vendorRefundedData[$vendorId]['qty_refunded'] == 0)) {
                if ($this->shippingHelper->getCalculateShippingBy() == 'overall_cart') {
                    return $this->emptyRefundShipping($order, $creditmemo);
                }
                if (in_array($vendorId, $vendorDeducted)) {
                    continue;
                }
                $vendorShippingCollection = $this->vendorShippingCollection->create()
                    ->addFieldToFilter('vendor_id', $vendorId)
                    ->addFieldToFilter('order_id', $item->getOrderId());
                if ($vendorShippingCollection->getSize() > 0) {
                    foreach ($vendorShippingCollection->getItems() as $vendorShipping) {
                        $shippingAmountDeduct += $vendorShipping->getShippingAmount();
                        $baseShippingAmountDeduct += $vendorShipping->getBaseShippingAmount();
                        $shippingInclTaxDeduct += $vendorShipping->getShippingInclTax();
                        $baseShippingInclTaxDeduct += $vendorShipping->getBaseShippingInclTax();
                    }
                    $vendorDeducted[] = $vendorId;
                }
            }
        }
        $order->setShippingAmount($order->getShippingAmount() - $shippingAmountDeduct);
        $order->setBaseShippingAmount($order->getBaseShippingAmount() - $baseShippingAmountDeduct);
        $order->setShippingInclTax($order->getShippingInclTax() - $shippingInclTaxDeduct);
        $order->setBaseShippingInclTax($order->getBaseShippingInclTax() - $baseShippingInclTaxDeduct);
        $creditmemo->setOrder($order);
        if($creditmemo->getInvoice() && $creditmemo->getInvoice()->getId()){
            $baseInvoiceAllowedAmount = $this->getShippingAmount($creditmemo);
            $creditmemo->setShippingAmount($baseInvoiceAllowedAmount);
            $creditmemo->setBaseShippingAmount($baseInvoiceAllowedAmount);
        }

        return [$creditmemo];
    }

    /**
     * @param $creditmemo
     * @return float
     */
    private function getShippingAmount($creditmemo)
    {
        $order = $creditmemo->getOrder();
        $invoice = $creditmemo->getInvoice();
        $isShippingInclTax = $this->taxConfig->displaySalesShippingInclTax($order->getStoreId());
        if ($isShippingInclTax) {
            $amount = $order->getBaseShippingInclTax() -
                $order->getBaseShippingRefunded() -
                $order->getBaseShippingTaxRefunded();
            $amount = min($amount, $creditmemo->getBaseShippingAmount());
        } else {
            $amount = $order->getBaseShippingAmount() - $order->getBaseShippingRefunded();
            $amount = min($amount, $invoice->getBaseShippingAmount(),$creditmemo->getBaseShippingAmount());
        }

        return (float)$amount;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo\Total\Shipping $subject
     * @param \Magento\Sales\Model\Order\Creditmemo\Total\Shipping $result
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return \Magento\Sales\Model\Order\Creditmemo\Total\Shipping
     */
    public function afterCollect(
        \Magento\Sales\Model\Order\Creditmemo\Total\Shipping $subject,
        \Magento\Sales\Model\Order\Creditmemo\Total\Shipping $result,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ) {
        $order = $creditmemo->getOrder();
        if ($order->getShippingAmount() != $order->getOrigData('shipping_amount')) {
            $order->setShippingAmount($order->getOrigData('shipping_amount'));
            $order->setBaseShippingAmount($order->getOrigData('base_shipping_amount'));
            $order->setShippingInclTax($order->getOrigData('shipping_incl_tax'));
            $order->setBaseShippingInclTax($order->getOrigData('base_shipping_incl_tax'));
            $creditmemo->setOrder($order);
        }
        return $result;
    }

    protected function emptyRefundShipping($order, $creditmemo)
    {
        $order->setShippingAmount(0);
        $order->setBaseShippingAmount(0);
        $order->setShippingInclTax(0);
        $order->setBaseShippingInclTax(0);
        $creditmemo->setBaseShippingAmount(0);
        $creditmemo->setOrder($order);
        return [$creditmemo];
    }

    protected function getDataProcessing($orderId){
        $adapter = $this->resource->getConnection('core_write');
        $vendorShippingTable = $this->resource->getTableName('omnyfy_mcm_vendor_shipping');
        $salesOrderItemTable = $this->resource->getTableName('sales_order_item');
        $select = $adapter->select()->from(
            ['vo' => 'omnyfy_mcm_vendor_order'], ['vo.vendor_id', 'refund_shipping_amount']
        )->join(
            ['vs' => $vendorShippingTable],
            'vo.order_id = vs.order_id and vo.vendor_id = vs.vendor_id',
            ['total_shipping_amount' => 'SUM(vs.shipping_incl_tax)']
        )->where(
            "vo.order_id = ?", (int) $orderId
        )->group(['vo.vendor_id', 'vo.order_id']);
        $result = [];
        foreach ($adapter->fetchAll($select) as $data) {
            $result[$data['vendor_id']] = $data;
        }
        return $result;
    }
}
