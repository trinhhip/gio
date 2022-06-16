<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class RebateInvoice
 * @package Omnyfy\RebateCore\Model
 */
class RebateInvoice extends AbstractModel
{
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_rebate_invoice';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_rebate_invoice';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\RebateInvoice::class);
    }
}
