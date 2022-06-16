<?php

namespace Omnyfy\RebateCore\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ITransactionRebateRepository
 * @package Omnyfy\RebateCore\Api\Data
 */
interface ITransactionRebateRepository
{

    public function getMaturedVendorRebateTransactions($vendorId, $orderId);

    public function startProcess($transcationsId);

    public function failedProcess($transcationsId);

    public function commitProcess($transcationsId);
}
