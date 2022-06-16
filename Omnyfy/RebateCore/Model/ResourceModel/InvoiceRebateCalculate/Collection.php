<?php

namespace Omnyfy\RebateCore\Model\ResourceModel\InvoiceRebateCalculate;

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
        $this->_init(\Omnyfy\RebateCore\Model\InvoiceRebateCalculate::class, \Omnyfy\RebateCore\Model\ResourceModel\InvoiceRebateCalculate::class);
    }


    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['secondTable' => $this->getTable('omnyfy_mcm_vendor_order')],
            '(main_table.vendor_id = secondTable.vendor_id AND main_table.order_id = secondTable.order_id)',
            ['vendor_total','vendor_total_incl_tax'] 
        );
    }
}

