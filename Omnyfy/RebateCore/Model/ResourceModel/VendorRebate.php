<?php

namespace Omnyfy\RebateCore\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class VendorRebate
 * @package Omnyfy\RebateCore\Model\ResourceModel
 */
class VendorRebate extends AbstractDb
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
        $this->_init('omnyfy_vendor_rebate', 'entity_id');
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
    public function getChangeRequestTable()
    {
        return 'omnyfy_rebate_change_request';
    }

    /**
     * @param \Magento\Framework\DataObject $valuesToInsert
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveChangeRequestData(\Magento\Framework\DataObject $valuesToInsert)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($valuesToInsert, $this->getChangeRequestTable());
        $connection->insert($this->getChangeRequestTable(), $data);
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $valuesToInsert
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteChangeRequestData($vendorRebateId)
    {
        $connection = $this->getConnection();
        return $connection->delete($this->getChangeRequestTable(), ["vendor_rebate_id = $vendorRebateId"]);
    }

    /**
     * @param $rebateId
     * @return mixed
     */
    public function loadChangeRequest($vendorRebateId)
    {
        $adapter = $this->getConnection();
        $changeRequestTable = $this->getTable($this->getChangeRequestTable());
        $select = $adapter->select()->from(
            ['mainTbl' => $changeRequestTable],
            ['*']
        )
        ->where(
            'mainTbl.vendor_rebate_id = ?', $vendorRebateId
        );
        return $adapter->fetchRow($select);
    }
}

