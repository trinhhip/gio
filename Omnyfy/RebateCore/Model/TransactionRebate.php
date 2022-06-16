<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class TransactionRebate
 * @package Omnyfy\RebateCore\Model
 */
class TransactionRebate extends AbstractModel
{
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_rebate_transaction';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_rebate_transaction';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\TransactionRebate::class);
    }
}
