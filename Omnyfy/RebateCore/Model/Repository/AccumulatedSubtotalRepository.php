<?php

namespace Omnyfy\RebateCore\Model\Repository;

use Exception;
use Omnyfy\RebateCore\Api\Data\IAccumulatedSubtotalRepository;
use Omnyfy\RebateCore\Model\AccumulatedSubtotalFactory;
use Omnyfy\RebateCore\Model\ResourceModel\AccumulatedSubtotal;


/**
 * Class AccumulatedSubtotalRepository
 * @package Omnyfy\RebateCore\Model\Repository
 */
class AccumulatedSubtotalRepository implements IAccumulatedSubtotalRepository
{

    /**
     * Name of Main Table.
     *
     * @var string
     */
    protected $mainTable = 'omnyfy_rebate_order_accumulation';
    /**
     * @var AccumulatedSubtotalFactory
     */
    private $accumulatedSubtotalFactory;

    /**
     * @var AccumulatedSubtotal
     */
    private $resource;

    /**
     * AccumulatedSubtotalRepository constructor.
     * @param AccumulatedSubtotalFactory $AccumulatedSubtotalFactory
     * @param Rebate $resource
     */
    public function __construct(
        AccumulatedSubtotalFactory $accumulatedSubtotalFactory,
        AccumulatedSubtotal $resource
    )
    {
        $this->accumulatedSubtotalFactory = $accumulatedSubtotalFactory;
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
     * @param rebate $modelAccumulatedSubtotal
     * @return rebate|null
     */
    public function saveAccumulatedSubtotal($modelAccumulatedSubtotal)
    {
        try {
            $modelAccumulatedSubtotal = $modelAccumulatedSubtotal->save();
            return $modelAccumulatedSubtotal;
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
    public function getAccumulatedSubtotal($id = null)
    {
        $model = $this->accumulatedSubtotalFactory->create();
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
    public function getAccumulatedSubtotalByVendorAndDate($vendorId, $rebateVendorId, $date)
    {
        $collection = $this->getAccumulatedSubtotal()->getCollection()->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        $collection->addFieldToFilter('rebate_vendor_id', ['eq' => $rebateVendorId]);
        $collection->addFieldToFilter('start_date', array('lteq' => $date));
        $collection->addFieldToFilter('payout_date', array('gteq' => $date));
        return $collection->getFirstItem();
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getAccumulatedSubtotalByFrequency($paymentFrequency, $listRebate = [])
    {
        $collection = $this->getAccumulatedSubtotal()->getCollection()->addFieldToFilter('payment_frequency', ['eq' => $paymentFrequency]);
        if (!empty($listRebate)) {
            $collection->addFieldToFilter('rebate_vendor_id', ['in' => $listRebate]);
        }
        return $collection;
    }
}
