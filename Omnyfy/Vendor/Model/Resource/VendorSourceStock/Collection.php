<?php

namespace Omnyfy\Vendor\Model\Resource\VendorSourceStock;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'omnyfy_vendor_source_stock';
    protected $_eventObject = 'vendor_source_stock_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Vendor\Model\VendorSourceStock', 'Omnyfy\Vendor\Model\Resource\VendorSourceStock');
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['is' => 'inventory_source'],
            'is.source_code = main_table.source_code'
        );
        $this->getSelect()->joinLeft(
            ['vendor' => $this->getTable('omnyfy_vendor_vendor_entity')],
            'main_table.vendor_id = vendor.entity_id',
            ['vendor_name' => 'vendor.name']
        );

        $this->addFilterToMap('vendor_id', 'main_table.vendor_id');
    }

    public function joinInventory() {
        $this->getSelect()->join(
            ['ovi' => 'omnyfy_vendor_inventory'],
            'main_table.id = ovi.source_stock_id',
            '*'
        );
    }

    public function joinSourceStockLink() {
        $this->getSelect()->join(
            ['link' => 'inventory_source_stock_link'],
            'main_table.stock_id = link.stock_id AND main_table.source_code = link.source_code',
            ['priority' => 'link.priority']
        );
    }
}
