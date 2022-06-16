<?php

namespace Omnyfy\RebateCore\Model\Repository;

use Exception;
use Omnyfy\RebateCore\Api\Data\IRebateInvoiceRepository;
use Omnyfy\RebateCore\Model\RebateInvoiceFactory;
use Omnyfy\RebateCore\Model\ResourceModel\RebateInvoice;
use Magento\Framework\Event\ManagerInterface as EventManager;


/**
 * Class RebateInvoiceRepository
 * @package Omnyfy\RebateCore\Model\Repository
 */
class RebateInvoiceRepository implements IRebateInvoiceRepository
{

    /**
     * Name of Main Table.
     *
     * @var string
     */
    protected $mainTable = 'omnyfy_rebate_invoice_item';
    /**
     * @var RebateFactory
     */
    private $rebateInvoiceFactory;

    /**
     * @var Rebate
     */
    private $resource;

    /**
    * @var EventManager
    */
    private $eventManager;

    /**
     * RebateInvoiceRepository constructor.
     * @param RebateFactory $rebateInvoiceFactory
     * @param Rebate $resource
     */
    public function __construct(
        RebateInvoiceFactory $rebateInvoiceFactory,
        EventManager $eventManager,
        RebateInvoice $resource
    )
    {
        $this->rebateInvoiceFactory = $rebateInvoiceFactory;
        $this->resource = $resource;
        $this->eventManager = $eventManager;
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
    public function saveRebateInvoice($modelRebateInvoice)
    {
        try {
            $modelRebateInvoice = $modelRebateInvoice->save();
            $this->eventManager->dispatch('omnyfy_invoice_rebate_save_after', ['invoiceRebate' => $modelRebateInvoice]);
            return $modelRebateInvoice;
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
    public function getRebateInvoice($id = null)
    {
        $model = $this->rebateInvoiceFactory->create();
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
    public function getAllRebatesInvoice()
    {
        $collection = $this->getRebateInvoice()->getCollection();
        return $collection;
    }

    /**
     * Insert new tier prices for processed product
     *
     * @param int $productId
     * @param array $valuesToInsert
     * @return bool
     */
    public function insertValues(int $invoiceId, array $valuesToInsert)
    {
        $isChanged = false;
        foreach ($valuesToInsert as $data) {
            $invoiceItem = new \Magento\Framework\DataObject($data);
            $invoiceItem->setData(
                'invoice_rebate_id',
                $invoiceId
            );
            $this->resource->saveInvoiceItemsData($invoiceItem);
            $isChanged = true;
        }

        return $isChanged;
    }

    /**
     * @param $rebateId
     * @return mixed
     */
    public function loadInvoiceItemByRebate($invoiceId)
    {
        return $this->resource->loadInvoiceItemByRebate($invoiceId);
    }

}
