<?php
namespace Omnyfy\VendorAuth\Model;

class EndpointAllowlist extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Omnyfy\VendorAuth\Model\ResourceModel\EndpointAllowlist');
    }
}