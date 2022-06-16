<?php

namespace Omnyfy\RebateCore\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class InvoiceRebateCalculate
 * @package Omnyfy\RebateCore\Model\ResourceModel
 */
class InvoiceRebateCalculate extends AbstractDb
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
        $this->_init('omnyfy_rebate_order_invoice', 'entity_id');
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
    public function getTransactionRebateTable()
    {
        return 'omnyfy_rebate_transaction';
    }

    /**
     * @param \Magento\Framework\DataObject $itemObject
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveItemData(\Magento\Framework\DataObject $itemObject)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($itemObject, $this->getTransactionRebateTable());
        $connection->insert($this->getTransactionRebateTable(), $data);
        return $this;
    }

    public function getCostItems($orderId, $vendorId) {
        $adapter = $this->getConnection();
        $table = $this->getTable('sales_order_item');
        $select = $adapter->select()->from(
            $table, [
                'vendor_id', 'order_id', 'base_cost', 'qty_ordered'
        ])->where(
            "vendor_id = ?", (int) $vendorId
        )->where(
            "order_id = ?", (int) $orderId
        );
        return $adapter->fetchAll($select);
    }

    public function calculateCost($orderId, $vendorId) {
        $costItems = $this->getCostItems($orderId, $vendorId);
        $total = 0;
        foreach ($costItems as $costItem) {
            $total += $costItem['base_cost'] * $costItem['qty_ordered'];
        }
        
        return $total;
    }
}

