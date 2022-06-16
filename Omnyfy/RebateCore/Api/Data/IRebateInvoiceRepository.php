<?php

namespace Omnyfy\RebateCore\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface IRebateInvoiceRepository
 * @package Omnyfy\RebateCore\Api\Data
 */
interface IRebateInvoiceRepository
{

    /**
     * Get RebateInvoice By Id
     */
    public function getRebateInvoice($id = null);

    /**
     * Get RebateInvoice
     */
    public function getAllRebatesInvoice();

    /**
     * Save RebateInvoice
     */
    public function saveRebateInvoice($modelRebateInvoice);


}
