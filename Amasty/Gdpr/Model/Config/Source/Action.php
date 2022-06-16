<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Action implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statusNames = [
            ['label' => __('Delete Request Submitted'), 'value' => 'delete_request_submitted'],
            ['label' => __('Delete Request Approved'), 'value' => 'delete_request_approved'],
            ['label' => __('Delete Request Denied'), 'value' => 'delete_request_denied'],
            ['label' => __('Data Anonymised by Customer'), 'value' => 'data_anonymised_by_customer'],
            ['label' => __('Data Anonymised by Admin'), 'value' => 'data_anonymised_by_admin'],
            ['label' => __('Personal Data Deleted by Admin'), 'value' => 'data_deleted_by_admin'],
        ];

        return $statusNames;
    }
}
