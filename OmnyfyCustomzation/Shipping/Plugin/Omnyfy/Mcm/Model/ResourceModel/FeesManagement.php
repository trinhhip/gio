<?php

namespace OmnyfyCustomzation\Shipping\Plugin\Omnyfy\Mcm\Model\ResourceModel;

/**
 * Class FeesManagement
 */
class FeesManagement
{
    function aroundGetVendorOrderTotals(
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $subject,
        $proceed,
        $vendorId,
        $orderId
    )
    {
        $adapter = $subject->getConnection();
        $table = $subject->getTable('omnyfy_mcm_vendor_order');
        $select = $adapter->select()->from(
            $table, [
                'vendor_id',
                'order_id',
                'subtotal',
                'base_subtotal',
                'subtotal_incl_tax',
                'base_subtotal_incl_tax',
                'tax_amount',
                'base_tax_amount',
                'discount_amount',
                'base_discount_amount',
                'shipping_amount',
                'base_shipping_amount',
                'shipping_incl_tax',
                'base_shipping_incl_tax',
                'shipping_tax',
                'base_shipping_tax',
                'shipping_discount_amount',
                'grand_total',
                'base_grand_total'
            ]
        )->where(
            "omnyfy_mcm_vendor_order.vendor_id = ?", (int)$vendorId
        )->where(
            "omnyfy_mcm_vendor_order.order_id = ?", (int)$orderId
        )->join('omnyfy_vendor_order_total',
            'omnyfy_vendor_order_total.order_id = omnyfy_mcm_vendor_order.order_id AND omnyfy_vendor_order_total.vendor_id = omnyfy_mcm_vendor_order.vendor_id',
            ['shipping_amount' => 'omnyfy_vendor_order_total.shipping_amount']);
        return $adapter->fetchRow($select);
    }
}
