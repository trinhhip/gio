<?php

namespace Omnyfy\RebateCore\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface IInvoiceRebateCalculateRepository
 * @package Omnyfy\RebateCore\Api\Data
 */
interface IInvoiceRebateCalculateRepository
{

    /**
     * Get Rebate Invoice By Id
     */
    public function getInvoiceRebateCalculate($id);

    /**
     * Get Rebate
     */
    public function getAllInvoiceRebateCalculates();

    /**
     * Save Rebate Invoice
     */
    public function saveInvoiceRebateCalculate($rebate);

    /**
     * delete Rebate Invoice
     */
    public function deleteInvoiceRebateCalculate($id);
}
