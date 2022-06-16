<?php


namespace OmnyfyCustomzation\Mcm\Helper;


class InvoiceHelper extends \Omnyfy\Mcm\Helper\InvoiceHelper
{
    public function saveInvoiceData($vendorIds, $orderId, $invoiceId)
    {
        $data = [];
        foreach ($vendorIds as $vendorId) {
            $vendorOrderTotals = $this->feesManagementResource->getVendorOrderTotals($vendorId, $orderId);
            if ($vendorOrderTotals){
                $data[] = [
                    'invoice_id' => $invoiceId,
                    'vendor_id' => $vendorId,
                    'order_id' => $orderId,
                    'subtotal' => $vendorOrderTotals['subtotal'],
                    'base_subtotal' => $vendorOrderTotals['base_subtotal'],
                    'subtotal_incl_tax' => $vendorOrderTotals['subtotal_incl_tax'],
                    'base_subtotal_incl_tax' => $vendorOrderTotals['base_subtotal_incl_tax'],
                    'tax_amount' => $vendorOrderTotals['tax_amount'] + $vendorOrderTotals['shipping_tax'],
                    'base_tax_amount' => $vendorOrderTotals['base_tax_amount'],
                    'discount_amount' => $vendorOrderTotals['discount_amount'] + $vendorOrderTotals['shipping_discount_amount'],
                    'base_discount_amount' => $vendorOrderTotals['base_discount_amount'] + $vendorOrderTotals['shipping_discount_amount'],
                    'shipping_amount' => $vendorOrderTotals['shipping_amount'],
                    'base_shipping_amount' => $vendorOrderTotals['base_shipping_amount'],
                    'shipping_incl_tax' => $vendorOrderTotals['shipping_incl_tax'],
                    'base_shipping_incl_tax' => $vendorOrderTotals['base_shipping_incl_tax'],
                    'shipping_tax' => $vendorOrderTotals['shipping_tax'],
                    'base_shipping_tax' => $vendorOrderTotals['base_shipping_tax'],
                    'shipping_discount_amount' => $vendorOrderTotals['shipping_discount_amount'],
                    'grand_total' => ($vendorOrderTotals['grand_total'] + $vendorOrderTotals['shipping_incl_tax'] - ($vendorOrderTotals['shipping_discount_amount'])),
                    'base_grand_total' => ($vendorOrderTotals['base_grand_total'] + $vendorOrderTotals['shipping_incl_tax'] - ($vendorOrderTotals['shipping_discount_amount'])),
                    'created_at' => $this->_date->gmtDate(),
                    'updated_at' => $this->_date->gmtDate(),
                ];
            }
        }
        $this->feesManagementResource->saveVendorInvoiceTotals($data);
    }
}
