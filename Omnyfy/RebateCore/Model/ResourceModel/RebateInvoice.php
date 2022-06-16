<?php

namespace Omnyfy\RebateCore\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Ui\Form\StatusInvoiceRebate;

/**
 * Class RebateInvoice
 * @package Omnyfy\RebateCore\Model\ResourceModel
 */
class RebateInvoice extends AbstractDb
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
        $this->_init('omnyfy_rebate_invoice', 'entity_id');
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return 'entity_id';
    }

    /**
     * @return string
     */
    public function getInvoiceItemTable()
    {
        return 'omnyfy_rebate_invoice_item';
    }

    /**
     * @param \Magento\Framework\DataObject $invoiceItemObject
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveInvoiceItemsData(\Magento\Framework\DataObject $invoiceItemObject)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($invoiceItemObject, $this->getInvoiceItemTable());

        if (!empty($data[$this->getIdFieldName()])) {
            $where = $connection->quoteInto($this->getIdFieldName() . ' = ?', $data[$this->getIdFieldName()]);
            unset($data[$this->getIdFieldName()]);
            $connection->update($this->getInvoiceItemTable(), $data, $where);
        } else {
            $connection->insert($this->getInvoiceItemTable(), $data);
        }
        return $this;
    }

    /**
     * @param $array
     * @return mixed
     */
    public function loadInvoiceItemByRebate($invoiceId)
    {
        $adapter = $this->getConnection();
        $contributionTbl = $this->getTable($this->getInvoiceItemTable());
        $select = $adapter->select()->from(
            ['mainTbl' => $contributionTbl],
            ['*']
        )
            ->where(
                'mainTbl.invoice_rebate_id = ?', $invoiceId
            );
        return $adapter->query($select);
    }
    
    /**
     *
     * @return int
     */
    public function sumTotalRebateMonthPending($vendorId, $status = 0)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount) as rebate_total_amount")
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "status = ?", $status
            )
            ->where(
                "payment_frequency = ?", PaymentFrequency::MONTHLY_AT_END_OF_MONTH
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalRebateAnnualPending($vendorId, $status = 0)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount) as rebate_total_amount")
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "status = ?", $status
            )
            ->where(
                "payment_frequency = ?", PaymentFrequency::ANNUALLY_ON_SPECIFIC_DATE
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

    /**
     *
     * @return int
     */
    public function sumTotalRebatePaid($vendorId)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), "SUM(rebate_total_amount) as rebate_total_amount")
            ->where(
                "vendor_id = ?", $vendorId
            )
            ->where(
                "status = ?", StatusInvoiceRebate::PAID_STATUS
            );
        $result = $connection->fetchOne($select);
        return $result;
    }

}

