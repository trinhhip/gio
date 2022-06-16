<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightClicks;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'click_id';

    protected function _construct()
    {
        $this->_init('Omnyfy\VendorFeatured\Model\SpotlightClicks', 'Omnyfy\VendorFeatured\Model\ResourceModel\SpotlightClicks');
    }
}