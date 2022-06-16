<?php

namespace Omnyfy\RebateCore\Model\Repository;

use Exception;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;
use Omnyfy\RebateCore\Model\VendorRebateFactory;
use Omnyfy\RebateCore\Model\ResourceModel\VendorRebate;


/**
 * Class RebateRepository
 * @package Omnyfy\RebateCore\Model\Repository
 */
class VendorRebateRepository implements IVendorRebateRepository
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
     * Name of Main Table.
     *
     * @var string
     */
    protected $mainTable = 'omnyfy_vendor_rebate';
    /**
     * @var RebateFactory
     */
    private $vendorRebateFactory;

    /**
     * @var Rebate
     */
    private $resource;

    /**
     * RebateRepository constructor.
     * @param RebateFactory $vendorRebateFactory
     * @param Rebate $resource
     */
    public function __construct(
        VendorRebateFactory $vendorRebateFactory,
        VendorRebate $resource
    )
    {
        $this->vendorRebateFactory = $vendorRebateFactory;
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
    public function saveVendorRebate($modelVendorRebate)
    {
        try {
            $this->resource->save($modelVendorRebate);
            return $modelVendorRebate;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param null $id
     * @return mixed
     */
    public function getRebateVendor($id = null)
    {
        $model = $this->vendorRebateFactory->create();
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
    public function getRebateByVendorActive($vendorId)
    {
        $collection = $this->getRebateVendor()->getCollection()->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        return $collection;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getRebateByVendorActiveAndEnable($vendorId)
    {
        $collection = $this->getRebateVendor()->getCollection()
        ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
        ->addFieldToFilter('lock_status', ['eq' => $this::ENABLE]);
        return $collection;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getRebateByVendorAndFrequency($vendorId, $lockPaymentFrequency)
    {
        $collection = $this->getRebateVendor()->getCollection()->addFieldToFilter('vendor_id', ['eq' => $vendorId])->addFieldToFilter('lock_payment_frequency', ['eq' => $lockPaymentFrequency]);
        return $collection;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getRebateByFrequency($lockPaymentFrequency)
    {
        $collection = $this->getRebateVendor()->getCollection()->addFieldToFilter('lock_payment_frequency', ['eq' => $lockPaymentFrequency]);
        return $collection;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function checkRebateByVendorActive($vendorId, $rebateId)
    {
        $collection = $this->getRebateVendor()->getCollection()->addFieldToFilter('vendor_id', ['eq' => $vendorId])->addFieldToFilter('rebate_id', ['eq' => $rebateId]);
        return ($collection->getSize() > 0) ? true : false;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getRebateVendorByIdRebate($rebateId)
    {
        $collection = $this->getRebateVendor()->getCollection()->addFieldToFilter('rebate_id', ['eq' => $rebateId]);
        return $collection;
    }

    /**
     * Insert new tier prices for processed product
     *
     * @param int $productId
     * @param array $valuesToInsert
     * @return bool
     */
    public function insertValues(array $valuesToInsert)
    {
        $data = new \Magento\Framework\DataObject($valuesToInsert);
        $this->resource->saveChangeRequestData($data);
    }

    /**
     * @param $rebateId
     * @return mixed
     */
    public function loadChangeRequest($vendorRebateId)
    {
        return $this->resource->loadChangeRequest($vendorRebateId);
    }

    /**
     * @param $rebateId
     * @return Rebate
     */
    public function deleteChangeRequestData($vendorRebateId)
    {
        return $this->resource->deleteChangeRequestData($vendorRebateId);
    }

}
