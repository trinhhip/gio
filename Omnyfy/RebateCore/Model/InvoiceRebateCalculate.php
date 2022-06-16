<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class InvoiceRebateCalculate
 * @package Omnyfy\RebateCore\Model
 */
class InvoiceRebateCalculate extends AbstractModel
{
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_rebate_order_invoice';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_rebate_order_invoice';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\InvoiceRebateCalculate::class);
    }
}
