<?php

namespace OmnyfyCustomzation\Vendor\Block\Vendor\Listing;

use Magento\Framework\View\Element\Template;
use Omnyfy\Vendor\Helper\Media;
use Omnyfy\Vendor\Model\VendorFactory;
use OmnyfyCustomzation\Vendor\Helper\Data;

class Title extends \Omnyfy\Vendor\Block\Vendor\Listing
{
    public function __construct(
        Template\Context $context,
        VendorFactory $vendorFactory,
        Media $helper,
        array $data = []
    )
    {
        parent::__construct($context, $vendorFactory, $helper, $data);
    }

    public function getCountVendor() {
        return $this->getLoadedVendorCollection()->getSize();
    }
}
