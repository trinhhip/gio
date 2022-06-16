<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ThresholdStatus
 * @package Omnyfy\RebateCore\Model
 */
class ThresholdStatus extends AbstractModel
{
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_rebate_accumulation_threshold_status';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_rebate_accumulation_threshold_status';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\ThresholdStatus::class);
    }
}
