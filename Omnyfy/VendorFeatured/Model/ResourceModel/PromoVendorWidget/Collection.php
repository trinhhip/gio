<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel\PromoVendorWidget;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Omnyfy\VendorFeatured\Model\PromoVendorWidget', 'Omnyfy\VendorFeatured\Model\ResourceModel\PromoVendorWidget');
    }

    protected function _initSelect() {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['ve' => $this->getTable('omnyfy_vendor_vendor_entity')],
            'main_table.vendor_id = ve.entity_id',
            [
                'vendor_name' => 've.name',
            ]
        );
    }
}