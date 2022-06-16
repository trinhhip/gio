<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class AccumulatedSubtotal
 * @package Omnyfy\RebateCore\Model
 */
class AccumulatedSubtotal extends AbstractModel
{
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_rebate_order_accumulation';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_rebate_order_accumulation';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\AccumulatedSubtotal::class);
    }
}
