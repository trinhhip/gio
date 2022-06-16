<?php

namespace Omnyfy\RebateCore\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ItemInvoiceRebate
 * @package Omnyfy\RebateCore\Model
 */
class ItemInvoiceRebate extends AbstractModel
{
    /**
     *
     */
    const CACHE_TAG = 'omnyfy_rebate_invoice_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'omnyfy_rebate_invoice_item';
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\ResourceModel\ItemInvoiceRebate::class);
    }
}
