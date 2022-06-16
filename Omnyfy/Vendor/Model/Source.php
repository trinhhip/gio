<?php

namespace Omnyfy\Vendor\Model;

class Source extends \Magento\Inventory\Model\Source
{
    public function getVendorId() {
        return $this->getData('vendor_id');
    }

    public function setVendorId(?string $vendorId) {
        $this->setData('vendor_id', $vendorId);
    }
}