<?php

namespace Omnyfy\Mcm\Model\ResourceModel;

class OrderRefund extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
    /**
     * Define main table
     */
    protected function _construct() {
        $this->_init('omnyfy_mcm_vendor_order', 'payout_id');
    }

    public function getVendorOrderRefundTotals($orderId) {
        $adapter = $this->getConnection();
        $vendorShippingTable = $this->getTable('omnyfy_mcm_vendor_shipping');
        $salesOrderItemTable = $this->getTable('sales_order_item');
        $select = $adapter->select()->from(
            ['vo' => $this->getMainTable()], ['vo.vendor_id', 'refund_shipping_amount']
        )->joinLeft(
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
        $select = $adapter->select()->from(
            ['vo' => $this->getMainTable()], ['vo.vendor_id']
        )->join(
            ['soi' => $salesOrderItemTable],
            'vo.order_id = soi.order_id and vo.vendor_id = soi.vendor_id',
            ['qty_ordered' => 'SUM(qty_ordered)', 'qty_refunded' => 'SUM(qty_refunded)']
        )->where(
            "vo.order_id = ?", (int) $orderId
        )->group(['vo.vendor_id', 'vo.order_id']);
        foreach ($adapter->fetchAll($select) as $data) {
            if(isset($data['vendor_id'])) {
                $result[$data['vendor_id']] = array_merge($result[$data['vendor_id']], $data);
            }
        }
        return $result;
    }

    public function getOrderRefundTotals($orderId) {
        $adapter = $this->getConnection();
        $salesOrderItemTable = $this->getTable('sales_order_item');
        $select = $adapter->select()->from(
            ['soi' => $salesOrderItemTable], ['qty_ordered' => 'SUM(qty_ordered)', 'qty_refunded' => 'SUM(qty_refunded)']
        )->where(
            "soi.order_id = ?", (int) $orderId
        )->group('soi.order_id');
        return $adapter->fetchRow($select);
    }
}