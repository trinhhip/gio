<?php

namespace Omnyfy\RebateCore\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Ui\Form\StatusTransactionRebate;

/**
 * Class TransactionRebate
 * @package Omnyfy\RebateCore\Model\ResourceModel
 */
class TransactionRebate extends AbstractDb
{
    /**
     * Rebate constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('omnyfy_rebate_transaction', 'entity_id');
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return 'entity_id';
    }

    /**
     *
     * @return int
     */
    public function sumTotalRebateByRebateVendor($reabteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where(
                "vendor_rebate_id = ?", $reabteId
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function getSumTotalRebatePaidByRebateVendor($reabteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where(
                "vendor_rebate_id = ?", $reabteId
            )
            ->where(
                "status = ?", StatusTransactionRebate::INVOICE_STATUS
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function getSumNetRebatePaidByRebateVendor($reabteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_net_amount)")
            ->where(
                "vendor_rebate_id = ?", $reabteId
            )
            ->where(
                "status = ?", StatusTransactionRebate::INVOICE_STATUS
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function getSumTaxRebatePaidByRebateVendor($reabteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_tax_amount)")
            ->where(
                "vendor_rebate_id = ?", $reabteId
            )
            ->where(
                "status = ?", StatusTransactionRebate::INVOICE_STATUS
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalRebateVendorAndInvoice($reabteId, $rebateVendorInvoiceId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where(
                "vendor_rebate_id = ?", $reabteId
            )
            ->where(
                "rebate_order_invoice_id = ?", $rebateVendorInvoiceId
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumNetRebateByRebateVendor($reabteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_net_amount)")
            ->where(
                "vendor_rebate_id = ?", $reabteId
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTaxRebateByRebateVendor($reabteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_tax_amount)")
            ->where(
                "vendor_rebate_id = ?", $reabteId
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalRebateByVendor($vendorId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "status != ?", StatusTransactionRebate::PROCESSING_STATUS
            )
            ->where(
                "status != ?", StatusTransactionRebate::INVOICE_STATUS
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalRebateByVendorAndOrder($vendorId, $orderId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "order_id = ?", $orderId
            )
            ->where(
                "status != ?", StatusTransactionRebate::INVOICE_STATUS
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalReadyRebateByVendor($vendorId, $status = 1)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "status = ?", $status
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalPayoutOrderRebateByVendor($vendorId, $status = 1)
    {
        $orderIds = $this->getOrderReadyRebateByVendor($vendorId);
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where("vendor_id = ?", $vendorId)
            ->where("status = ?", $status)
            ->where('order_id IN (?)', $orderIds);

        $result = $connection->fetchOne($select);
        return $result;
    }

    public function getOrderReadyRebateByVendor($vendorId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getTable('omnyfy_mcm_vendor_order'), "order_id")
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "payout_status = ?", 0
            )
            ->where(
                "payout_action = ?", 1
            );
        $orders = $connection->fetchAll($select);
        $result = [];
        foreach ($orders as $order) {
            $result[] = $order['order_id'];
        }
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalPendingOrderRebateByVendor($vendorId, $status = 1)
    {
        $orderIds = $this->getOrderPendingRebateByVendor($vendorId);
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where("vendor_id = ?", $vendorId)
            ->where("status = ?", $status)
            ->where('order_id IN (?)', $orderIds);

        $result = $connection->fetchOne($select);
        return $result;
    }

    public function getOrderPendingRebateByVendor($vendorId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getTable('omnyfy_mcm_vendor_order'), "order_id")
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "payout_status = ?", 0
            )
            ->where(
                "payout_action = ?", 0
            );
        $orders = $connection->fetchAll($select);
        $result = [];
        foreach ($orders as $order) {
            $result[] = $order['order_id'];
        }
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalRebatePaidSettlement($vendorId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where(
                "payment_frequency = ?", PaymentFrequency::PER_ORDER_SETTLEMENT
            )
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "status = ?", StatusTransactionRebate::INVOICE_STATUS
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

}

