<?php

namespace Omnyfy\RebateCore\Model\Repository;

use Exception;
use Omnyfy\RebateCore\Api\Data\IInvoiceRebateCalculateRepository;
use Omnyfy\RebateCore\Model\InvoiceRebateCalculateFactory;
use Omnyfy\RebateCore\Model\ResourceModel\InvoiceRebateCalculate;


/**
 * Class InvoiceRebateCalculateRepository
 * @package Omnyfy\RebateCore\Model\Repository
 */
class InvoiceRebateCalculateRepository implements IInvoiceRebateCalculateRepository
{

    /**
     * Name of Main Table.
     *
     * @var string
     */
    protected $mainTable = 'omnyfy_rebate_order_invoice';
    /**
     * @var invoiceRebateFactory
     */
    private $invoiceRebateFactory;

    /**
     * @var InvoiceRebateCalculate
     */
    private $resource;

    /**
     * InvoiceRebateCalculateRepository constructor.
     * @param invoiceRebateFactory $invoiceRebateFactory
     * @param Rebate $resource
     */
    public function __construct(
        InvoiceRebateCalculateFactory $invoiceRebateFactory,
        InvoiceRebateCalculate $resource
    )
    {
        $this->invoiceRebateFactory = $invoiceRebateFactory;
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
     * @param rebate $modelInvoicerebate
     * @return rebate|null
     */
    public function saveInvoiceRebateCalculate($modelInvoiceRebateCalculate)
    {
        try {
            $modelInvoiceRebateCalculate = $modelInvoiceRebateCalculate->save();
            return $modelInvoiceRebateCalculate;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Delete
     *
     * @param rebate $modelInvoicerebate
     * @return bool
     */
    public function deleteInvoiceRebateCalculate($modelInvoicerebate)
    {
        try {
            $this->resource->delete($modelInvoicerebate);
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
    public function getInvoiceRebateCalculate($id = null)
    {
        $model = $this->invoiceRebateFactory->create();
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
    public function getAllInvoiceRebateCalculates()
    {
        $collection = $this->getInvoiceRebateCalculate()->getCollection();
        return $collection;
    }

    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getInvoiceRebateCalculatesIdByOrderAndVendor($orderId, $vendorId)
    {
        $collection = $this->getInvoiceRebateCalculate()->getCollection()->addFieldToFilter('main_table.order_id', ['eq' => $orderId])
            ->addFieldToFilter('main_table.vendor_id', ['eq' => $vendorId])->getFirstItem();
        return $collection;
    }
    /**
     *
     * @param $reabate
     * @return Rebate
     */
    public function getInvoiceRebateByCreateAt($createAt)
    {
        $collection = $this->getInvoiceRebateCalculate()->getCollection()->addFieldToFilter('created_at', ['gteq' => $createAt]);
        return $collection;
    }
    /**
     * Insert new tier prices for processed product
     *
     * @param int $productId
     * @param array $valuesToInsert
     * @return bool
     */
    public function insertValues(int $rebateInvoiceId, array $valuesToInsert)
    {
        $isChanged = false;
        foreach ($valuesToInsert as $data) {
            $item = new \Magento\Framework\DataObject($data);
            $item->setData(
                'rebate_order_invoice_id',
                $rebateInvoiceId
            );
            $this->resource->saveItemData($item);
            $isChanged = true;
        }

        return $isChanged;
    }
}
