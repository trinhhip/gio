<?php


namespace OmnyfyCustomzation\Vendor\Plugin\Vendor;


use Omnyfy\Vendor\Model\VendorFactory;
use OmnyfyCustomzation\Vendor\Helper\Url;

class Link
{
    /**
     * @var Url
     */
    protected $helperUrl;
    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * Link constructor.
     * @param Url $helperUrl
     * @param VendorFactory $vendorFactory
     */
    public function __construct(
        Url $helperUrl,
        VendorFactory $vendorFactory
    )
    {
        $this->helperUrl = $helperUrl;
        $this->vendorFactory = $vendorFactory;
    }

    public function afterGetVendorUrl(\Omnyfy\Vendor\Block\Customer\Vendor\Link $subject, $result, $vendorId)
    {
        $vendor = $this->vendorFactory->create()->load($vendorId);
        $vendorUrl = $this->helperUrl->getVendorUrl($vendor);
        return $vendorUrl ? $vendorUrl : $result;
    }
}