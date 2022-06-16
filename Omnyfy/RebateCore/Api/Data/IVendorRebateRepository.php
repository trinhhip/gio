<?php

namespace Omnyfy\RebateCore\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface IVendorRebateRepository
 * @package Omnyfy\RebateCore\Api\Data
 */
interface IVendorRebateRepository
{

    /**
     * Get Rebate By Vendor
     */
    public function getRebateByVendorActive($vendorId);

    /**
     * Save Rebate Active
     */
    public function saveVendorRebate($rebateActive);

}
