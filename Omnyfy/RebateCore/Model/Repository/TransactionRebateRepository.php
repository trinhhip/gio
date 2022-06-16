<?php

namespace Omnyfy\RebateCore\Model\Repository;

use Exception;
use Omnyfy\RebateCore\Api\Data\ITransactionRebateRepository;
use Omnyfy\RebateCore\Model\TransactionRebateFactory;
use Omnyfy\RebateCore\Model\ResourceModel\TransactionRebate;
use Omnyfy\RebateCore\Ui\Form\PaymentFrequency;
use Omnyfy\RebateCore\Ui\Form\StatusTransactionRebate;

/**
 * Class RebateRepository
 * @package Omnyfy\RebateCore\Model\Repository
 */
class TransactionRebateRepository implements ITransactionRebateRepository
{
    /**
     * Name of Main Table.
     *
     * @var string
     */
    protected $mainTable = 'omnyfy_rebate_transaction';
    /**
     * @var RebateFactory
     */
    private $transactionRebateFactory;

    /**
     * @var Rebate
     */
    private $resource;

    /**
     * RebateRepository constructor.
     * @param RebateFactory $transactionRebateFactory
     * @param Rebate $resource
     */
    public function __construct(
        TransactionRebateFactory $transactionRebateFactory,
        TransactionRebate $resource
    )
    {
        $this->transactionRebateFactory = $transactionRebateFactory;
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function getMainTable()
    {
        return $this->resource->getTable($this->mainTable);
    }

    /**
     * Save process
     *
     * @param rebate $modelrebate
     * @return rebate|null
     */
    public function saveTransactionRebate($transactionRebate)
    {
        try {
            $transactionRebate = $transactionRebate->save();
            return $transactionRebate;
        } catch (Exception $exception) {
            return false;
        }
    }


    /**
     * Get Banner by Id
     *
     * @param $id
     * @return Banner
     */
    public function getTransactionRebate($id = null)
    {
        $model = $this->transactionRebateFactory->create();
        if ($id) {
            $this->resource->load($model, $id);
        }
        return $model;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getAllRebates()
    {
        $collection = $this->getTransactionRebate()->getCollection();
        return $collection;
    }

    /**
     *
     * @param $transcationsId
     * @return transcationsId
     */
    public function startProcess($transcationsId)
    {
        $collection = $this->getTransactionRebate($transcationsId)->setData('status', StatusTransactionRebate::PROCESSING_STATUS)->save();
        return $collection;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function failedProcess($transcationsId)
    {
        $collection = $this->getTransactionRebate($transcationsId)->setData('status', StatusTransactionRebate::PENDING_STATUS)->save();
        return $collection;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function commitProcess($transcationsId)
    {
        $collection = $this->getTransactionRebate($transcationsId)->setData('status', StatusTransactionRebate::INVOICE_STATUS)->save();
        return $collection;
    }

    public function getMaturedVendorRebateTransactions($vendorId, $orderId){
        $adapter = $this->resource->getConnection();
        $select = $adapter->select()->from(
            ['mainTbl' => $this->resource->getMainTable()],
            ['entity_id','rebate_total_amount','vendor_rebate_id']
        )
        ->where(
            'vendor_id = ?', $vendorId
        )->where(
            'order_id = ?', $orderId
        )->where(
            'status = ?', StatusTransactionRebate::PENDING_STATUS
        )->where(
            'payment_frequency = ?', PaymentFrequency::PER_ORDER_SETTLEMENT
        );
        $data['rebate_transactions'] = $adapter->fetchAll($select);
        $total = 0;
        foreach ($data['rebate_transactions'] as $value) {
            $total += $value['rebate_total_amount'];
        }
        $data['total_transaction_amount'] = $total;
        $data['count'] = count($data['rebate_transactions']);
        return $data;
    }

    public function getPerOrderSettlementTransactions($vendorId, $orderId){
        $adapter = $this->resource->getConnection();
        $select = $adapter->select()->from(
            ['mainTbl' => $this->resource->getMainTable()],
            ['SUM(rebate_total_amount) AS rebate_total_amount']
        )
        ->where(
            'vendor_id = ?', $vendorId
        )->where(
            'order_id = ?', $orderId
        )->where(
            'payment_frequency = ?', PaymentFrequency::PER_ORDER_SETTLEMENT
        );
        $row = $adapter->fetchRow($select);
        return $row['rebate_total_amount'];
    }

    public function getVendorMonthGroupOrder(){
        $adapter = $this->resource->getConnection();
        $select = $adapter->select()->from(
            ['mainTbl' => $this->resource->getMainTable()],
            ['SUM(rebate_total_amount) as rebate_total_amount', 'SUM(rebate_tax_amount) as rebate_tax_amount','vendor_id', 'payment_frequency']
        )->where(
            'status = ?', StatusTransactionRebate::NO_ACTION_STATUS
        )->where(
            'payment_frequency = ?', PaymentFrequency::MONTHLY_AT_END_OF_MONTH
        )->group(['vendor_id']);
        return $adapter->fetchAll($select);
    }

    public function getVendorMonthOrderTotal($vendorId){
        $adapter = $this->resource->getConnection();
        $select = $adapter->select()->from(
            ['mainTbl' => $this->resource->getMainTable()],
            ['SUM(rebate_total_amount) as rebate_total_amount', 'SUM(rebate_tax_amount) as rebate_tax_amount', 'SUM(rebate_net_amount) as rebate_net_amount','vendor_rebate_id']
        )
        ->where(
            'vendor_id = ?', $vendorId
        )->where(
            'status = ?', StatusTransactionRebate::NO_ACTION_STATUS
        )->where(
            'payment_frequency = ?', PaymentFrequency::MONTHLY_AT_END_OF_MONTH
        )
        ->group(['vendor_id', 'vendor_rebate_id']);
        return $adapter->fetchAll($select);
    }

    public function updateTransactionVendorMonthOrder($vendorId){
        $adapter = $this->resource->getConnection();
        $data = ["status" => StatusTransactionRebate::INVOICE_STATUS];
        $where = [  'vendor_id = ?' => $vendorId,
                    'status = ?' => StatusTransactionRebate::NO_ACTION_STATUS,
                    'payment_frequency = ?' => PaymentFrequency::MONTHLY_AT_END_OF_MONTH
            ];
        $tableName = $adapter->getTableName("omnyfy_rebate_transaction");
        $adapter->update($tableName, $data, $where);
    }

    public function getVendorAnnualGroupOrder($vendorRebateId, $vendorId){
        $adapter = $this->resource->getConnection();
        $select = $adapter->select()->from(
            ['mainTbl' => $this->resource->getMainTable()],
            ['SUM(rebate_total_amount) as rebate_total_amount', 'SUM(rebate_tax_amount) as rebate_tax_amount', 'SUM(rebate_net_amount) as rebate_net_amount', 'vendor_id', 'payment_frequency', 'vendor_rebate_id']
        )->where(
            'status = ?', StatusTransactionRebate::NO_ACTION_STATUS
        )->where(
            'payment_frequency = ?', PaymentFrequency::ANNUALLY_ON_SPECIFIC_DATE
        )
        ->where(
            'vendor_rebate_id = ?', $vendorRebateId
        )
        ->where(
            'vendor_id = ?', $vendorId
        );
        return $adapter->fetchAll($select);
    }

    public function updateTransactionVendorAnnualOrder($vendorRebateId, $vendorId){
        $adapter = $this->resource->getConnection();
        $data = ["status" => StatusTransactionRebate::INVOICE_STATUS];
        $where = [  'vendor_id = ?' => $vendorId,
                    'status = ?' => StatusTransactionRebate::NO_ACTION_STATUS,
                    'payment_frequency = ?' => PaymentFrequency::ANNUALLY_ON_SPECIFIC_DATE,
                    'vendor_rebate_id = ?' => $vendorRebateId
            ];
        $tableName = $adapter->getTableName("omnyfy_rebate_transaction");
        $adapter->update($tableName, $data, $where);
    }

    public function getRebateByVendorAndOrderRefund($vendorId, $orderId){
        $collection = $this->getTransactionRebate()->getCollection();
        $collection->addFieldToFilter('vendor_id', ['eq' => $vendorId])
        ->addFieldToFilter('order_id', ['eq' => $orderId]);
        return $collection;
    }

    public function getRebateUpdateByVendorAndOrderRefund($vendorId, $orderId, $rebate){
        $collection = $this->getTransactionRebate()->getCollection();
        $collection->addFieldToFilter('vendor_id', ['eq' => $vendorId])
        ->addFieldToFilter('order_id', ['eq' => $orderId])
        ->addFieldToFilter('vendor_rebate_id', ['eq' => $rebate->getId()]);
        return $collection->getFirstItem();
    }
}
