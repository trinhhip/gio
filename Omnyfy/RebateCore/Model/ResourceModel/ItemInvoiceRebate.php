<?php

namespace Omnyfy\RebateCore\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Ui\Form\StatusInvoiceRebate;

/**
 * Class ItemInvoiceRebate
 * @package Omnyfy\RebateCore\Model\ResourceModel
 */
class ItemInvoiceRebate extends AbstractDb
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
        $this->_init('omnyfy_rebate_invoice_item', 'entity_id');
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
    public function getSumTotalRebatePaidByRebateVendor($reabteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount)")
            ->where(
                "vendor_rebate_id = ?", $reabteId
            )
            ->where(
                "invoice_rebate_id IN (?)", $this->getPaidInvoice()
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
                "invoice_rebate_id IN (?)", $this->getPaidInvoice()
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
                "invoice_rebate_id IN (?)", $this->getPaidInvoice()
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

     /**
     * @return string
     */
    public function getInvoiceTable()
    {
        return 'omnyfy_rebate_invoice';
    }

    /**
     *
     * @return int
     */
    public function getPaidInvoice()
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getInvoiceTable(), "entity_id")
            ->where(
                "status = ?", StatusInvoiceRebate::PAID_STATUS
            );
        $result = $connection->fetchAll($select);
        return $result;
    }

}

