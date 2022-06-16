<?php

namespace Omnyfy\RebateCore\Api\Data;

use \Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface IRebateRepository
 * @package Omnyfy\RebateCore\Api\Data
 */
interface IRebateRepository
{

    /**
     * Get Rebate By Id
     */
    public function getRebate($id);

    /**
     * Get Rebate
     */
    public function getAllRebates();

    /**
     * Save Rebate
     */
    public function saveRebate($rebate);

    /**
     * delete Rebate
     */
    public function deleteRebate($id);

    /**
     * disable Rebate
     */
    public function disableRebate($id);

}
