<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Rebate
 * @package Omnyfy\RebateCore\Model
 */
class Rebate extends AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'omnyfy_rebate';
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_rebate';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_rebate';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\Rebate::class);
    }
}
