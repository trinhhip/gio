<?php
namespace Omnyfy\Vendor\Model\Resource\Source;

class Collection extends \Magento\Inventory\Model\ResourceModel\Source\Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['ve' => 'omnyfy_vendor_vendor_entity'],
            'main_table.vendor_id = ve.entity_id',
            ['vendor_name' => 've.name']
        );
        $this->addFilterToMap('vendor_name', 've.name');
        $this->addFilterToMap('name', 'main_table.name');
    }
}
