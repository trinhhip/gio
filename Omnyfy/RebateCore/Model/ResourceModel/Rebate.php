<?php

namespace Omnyfy\RebateCore\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Rebate
 * @package Omnyfy\RebateCore\Model\ResourceModel
 */
class Rebate extends AbstractDb
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
        $this->_init('omnyfy_rebate', 'entity_id');
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
    public function getContributionTable()
    {
        return 'omnyfy_rebate_contribution';
    }

    /**
     * @param \Magento\Framework\DataObject $contributionObject
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveContributionsData(\Magento\Framework\DataObject $contributionObject)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable($contributionObject, $this->getContributionTable());

        if (!empty($data[$this->getIdFieldName()])) {
            $where = $connection->quoteInto($this->getIdFieldName() . ' = ?', $data[$this->getIdFieldName()]);
            unset($data[$this->getIdFieldName()]);
            $connection->update($this->getContributionTable(), $data, $where);
        } else {
            $connection->insert($this->getContributionTable(), $data);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $contributionObject
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteContributionsData($rebateId)
    {
        $connection = $this->getConnection();
        return $connection->delete($this->getContributionTable(), ["rebate_id = $rebateId"]);
    }

    /**
     * @param $rebateId
     * @return mixed
     */
    public function loadContributionByRebate($rebateId)
    {
        $adapter = $this->getConnection();
        $contributionTbl = $this->getTable($this->getContributionTable());
        $select = $adapter->select()->from(
            ['mainTbl' => $contributionTbl],
            ['*']
        )
            ->where(
                'mainTbl.rebate_id = ?', $rebateId
            );
        return $adapter->query($select);
    }

    /**
     * @param $rebateId
     * @param $contributionId
     * @return mixed
     */
    public function checkOptionContribution($rebateId, $contributionId)
    {
        $adapter = $this->getConnection();
        $contributionTbl = $this->getTable($this->getContributionTable());
        $select = $adapter->select()->from(
            ['mainTbl' => $contributionTbl],
            ['*']
        )
            ->where(
                'mainTbl.rebate_id = ?', $rebateId
            )
            ->where(
                'mainTbl.entity_id = ?', $contributionId
            )->limit(1);
        return $adapter->fetchRow($select);
    }
}

