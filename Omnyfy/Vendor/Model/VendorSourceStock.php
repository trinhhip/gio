<?php
namespace Omnyfy\Vendor\Model;

class VendorSourceStock extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'omnyfy_vendor_source_stock';

    protected function _construct()
    {
        $this->_init('Omnyfy\Vendor\Model\Resource\VendorSourceStock');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}