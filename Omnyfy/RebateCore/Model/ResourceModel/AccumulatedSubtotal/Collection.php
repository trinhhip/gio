<?php

namespace Omnyfy\RebateCore\Model\ResourceModel\AccumulatedSubtotal;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Omnyfy\RebateCore\Model\ResourceModel\InvoiceRebateCalculate
 */
class Collection extends AbstractCollection
{
    /**
     * Identifier field name for collection items
     *
     * Can be used by collections with items without defined
     *
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Omnyfy\RebateCore\Model\AccumulatedSubtotal::class, \Omnyfy\RebateCore\Model\ResourceModel\AccumulatedSubtotal::class);
    }
}

