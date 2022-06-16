<?php


namespace OmnyfyCustomzation\Vendor\Plugin\Vendor;


use OmnyfyCustomzation\Vendor\Helper\Url;

class Listing
{
    /**
     * @var Url
     */
    protected $helperUrl;

    /**
     * Listing constructor.
     * @param Url $helperUrl
     */
    public function __construct(
        Url $helperUrl
    )
    {
        $this->helperUrl = $helperUrl;
    }

    public function afterGetVendorUrl(\Omnyfy\Vendor\Block\Vendor\Listing $subject, $result, $vendor)
    {
        $vendorUrl = $this->helperUrl->getVendorUrl($vendor);
        return $vendorUrl ? $vendorUrl : $result;
    }
}
