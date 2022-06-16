<?php

namespace Omnyfy\RebateCore\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface IAccumulatedSubtotalRepository
 * @package Omnyfy\RebateCore\Api\Data
 */
interface IAccumulatedSubtotalRepository
{

    /**
     * Get Rebate By Id
     */
    public function getAccumulatedSubtotal($accumulatedSubtotalId);

    /**
     * Get Rebate By Id
     */
    public function getAccumulatedSubtotalByVendorAndDate($vendorId, $rebateVendorId, $date);

    /**
     * Save Rebate
     */
    public function saveAccumulatedSubtotal($accumulatedSubtotal);

}
