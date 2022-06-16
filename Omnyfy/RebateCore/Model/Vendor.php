<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Vendor
 * @package Omnyfy\RebateCore\Model
 */
class Vendor extends AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'omnyfy_vendor_vendor_entity_rebate';
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_vendor_vendor_entity';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_vendor_vendor_entity';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\Vendor::class);
    }
}
