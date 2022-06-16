<?php
namespace Omnyfy\VendorAuth\Model\ResourceModel\EndpointAllowlist;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Omnyfy\VendorAuth\Model\EndpointAllowlist', 'Omnyfy\VendorAuth\Model\ResourceModel\EndpointAllowlist');
    }
}