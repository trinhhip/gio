<?php

namespace Omnyfy\RebateCore\Model\Repository;

use Exception;
use Omnyfy\RebateCore\Api\Data\IRebateRepository;
use Omnyfy\RebateCore\Model\RebateFactory;
use Omnyfy\RebateCore\Model\ResourceModel\Rebate;


/**
 * Class RebateRepository
 * @package Omnyfy\RebateCore\Model\Repository
 */
class RebateRepository implements IRebateRepository
{
    /**
     *
     */
    const ENABLE = '1';
    /**
     *
     */
    const DISABLE = '0';
    /**
     *
     */
    const OMNYFY_REBATE_CONTRIBUTION = 'omnyfy_rebate_contribution';

    /**
     * Name of Main Table.
     *
     * @var string
     */
    protected $mainTable = 'omnyfy_rebate';
    /**
     * @var RebateFactory
     */
    private $rebateFactory;

    /**
     * @var Rebate
     */
    private $resource;

    /**
     * RebateRepository constructor.
     * @param RebateFactory $rebateFactory
     * @param Rebate $resource
     */
    public function __construct(
        RebateFactory $rebateFactory,
        Rebate $resource
    )
    {
        $this->rebateFactory = $rebateFactory;
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
    public function saveRebate($modelRebate)
    {
        try {
            $modelRebate = $modelRebate->save();
            return $modelRebate;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Delete
     *
     * @param rebate $modelrebate
     * @return bool
     */
    public function deleteRebate($modelrebate)
    {
        try {
            $this->resource->delete($modelrebate);
        } catch (Exception $exception) {
            return false;
        }

    }

    /**
     * disbaleRebate
     *
     * @param rebate $modelrebate
     * @return bool
     */
    public function disableRebate($modelrebate)
    {
        try {
            $modelrebate->setStatus(self::DISABLE);
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
    public function getRebate($id = null)
    {
        $model = $this->rebateFactory->create();
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
        $collection = $this->getRebate()->getCollection();
        return $collection;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getAllRebatesEnable()
    {
        $collection = $this->getRebate()->getCollection()->addFieldToFilter('status', ['eq' => $this::ENABLE]);
        return $collection;
    }

    /**
     * Insert new tier prices for processed product
     *
     * @param int $productId
     * @param array $valuesToInsert
     * @return bool
     */
    public function insertValues(int $rebateId, array $valuesToInsert)
    {
        $isChanged = false;
        foreach ($valuesToInsert as $data) {
            if (isset($data['entity_id'])) {
                unset($data['entity_id']);
            }
            $contribution = new \Magento\Framework\DataObject($data);
            $contribution->setData(
                'rebate_id',
                $rebateId
            );
            $this->resource->saveContributionsData($contribution);
            $isChanged = true;
        }

        return $isChanged;
    }

    /**
     * @param $rebateId
     * @return mixed
     */
    public function loadContributionByRebate($rebateId)
    {
        return $this->resource->loadContributionByRebate($rebateId);
    }

    /**
     * @param $rebateId
     * @return Rebate
     */
    public function deleteContributionsData($rebateId)
    {
        return $this->resource->deleteContributionsData($rebateId);
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function issetOptionContribution($rebateId, $contributionId)
    {
        $collection = $this->resource->checkOptionContribution($rebateId, $contributionId);
        return $collection ? $collection : false;
    }

}
