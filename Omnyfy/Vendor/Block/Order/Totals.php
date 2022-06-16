<?php
namespace Omnyfy\Vendor\Block\Order;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    protected $feesManagementResource;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource,
        array $data = []
    ) {
        $this->feesManagementResource = $feesManagementResource;
        parent::__construct($context, $registry, $data);
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $order = $this->getOrder();
        $vendorId = $this->getParentBlock()->getVendorId();
        if ($vendorId > 0) {
            $vendorOrderTotals = $this->feesManagementResource->getVendorOrderTotals($vendorId, $order->getId());

            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'vendor_subtotal',
                    'value' => $vendorOrderTotals['subtotal'],
                    'label' => 'Subtotal',
                ]
            );
            $this->addTotal($total);

            if ($vendorOrderTotals['shipping_amount'] > 0) {
                $total = new \Magento\Framework\DataObject(
                    [
                        'code' => 'vendor_shipping',
                        'value' => $vendorOrderTotals['shipping_amount'],
                        'label' => 'Shipping & Handling',
                    ]
                );
                $this->addTotal($total);
            }

            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'vendor_tax',
                    'value' => $vendorOrderTotals['tax_amount'] + $vendorOrderTotals['shipping_tax'],
                    'label' => 'Tax',
                ]
            );
            $this->addTotal($total);
            if ($vendorOrderTotals['discount_amount'] > 0) {
                $total = new \Magento\Framework\DataObject(
                    [
                        'code' => 'vendor_discount',
                        'value' => $vendorOrderTotals['discount_amount'] + $vendorOrderTotals['shipping_discount_amount'],
                        'label' => 'Discount',
                    ]
                );
                $this->addTotal($total);
            }

            $total = new \Magento\Framework\DataObject(
                [
                    'code' => 'vendor_grand_total',
                    'value' => $vendorOrderTotals['grand_total'] + $vendorOrderTotals['shipping_amount'] + $vendorOrderTotals['shipping_tax'] - $vendorOrderTotals['shipping_discount_amount'],
                    'label' => 'Grand Total',
                    'area' => 'footer',
                ]
            );
            $this->addTotal($total);
            $this->removeTotal('grand_total');
            $this->removeTotal('subtotal');
            $this->removeTotal('shipping');
            $this->removeTotal('discount');
            $this->removeTotal('base_grandtotal');
            $this->removeTotal('grand_total_incl');
            $this->removeTotal('tax');
        }
        return $this;
    }

}