<?php
/**
 * Project: Multi Vendor M2.
 * User: jing
 * Date: 12/7/17
 * Time: 4:36 PM
 */

namespace Omnyfy\Mcm\Model\Resource\Transaction;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Backend\Model\Session as BackendSession;

class GridCollection extends \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection
{

    protected $backendSession;

    public function __construct(
        BackendSession $backendSession,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager, Snapshot $entitySnapshot,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ){
        $this->backendSession = $backendSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $entitySnapshot, $connection, $resource);
    }

    protected function _renderFiltersBefore()
    {
        if ($this->_paymentId) {
            $this->getSelect()->where('main_table.payment_id = ?', $this->_paymentId);
        }
        if ($this->_parentId) {
            $this->getSelect()->where('main_table.parent_id = ?', $this->_parentId);
        }
        if ($this->_txnTypes) {
            $this->getSelect()->where('main_table.txn_type IN(?)', $this->_txnTypes);
        }
        if ($this->_orderId) {
            $this->getSelect()->where('main_table.order_id = ?', $this->_orderId);
        }
        if ($this->_addPaymentInformation) {
            $this->getSelect()->joinInner(
                ['sop' => $this->getTable('sales_order_payment')],
                'main_table.payment_id = sop.entity_id',
                $this->_addPaymentInformation
            );
        }
        if ($this->_storeIds) {
            $this->getSelect()->where('so.store_id IN(?)', $this->_storeIds);
            $this->addOrderInformation(['store_id']);
        }
        if ($this->_addOrderInformation) {
            $this->getSelect()->joinInner(
                ['so' => $this->getTable('sales_order')],
                'main_table.order_id = so.entity_id',
                $this->_addOrderInformation
            );
        }

        $vendorInfo = $this->backendSession->getVendorInfo();
        if(!empty($vendorInfo) && !empty($vendorInfo['vendor_id'])){
            $ovTable = 'omnyfy_vendor_vendor_order';
            $this->addFieldToFilter(
                'order_id',
                [
                    'in' => new \Zend_Db_Expr('SELECT order_id FROM ' . $ovTable . ' WHERE vendor_id=' . $vendorInfo['vendor_id'])
                ]
            );
        }
    }
}
