<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class VendorRebate
 * @package Omnyfy\RebateCore\Model
 */
class VendorRebate extends AbstractModel
{
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_vendor_rebate';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_vendor_rebate';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\VendorRebate::class);
    }
}
