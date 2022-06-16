<?php


namespace OmnyfyCustomzation\Vendor\Plugin\Vendor;


use OmnyfyCustomzation\Vendor\Helper\Url;

class Location
{
    /**
     * @var Url
     */
    protected $helperUrl;

    /**
     * Location constructor.
     * @param Url $helperUrl
     */
    public function __construct(
        Url $helperUrl
    )
    {
        $this->helperUrl = $helperUrl;
    }

    public function afterGetVendorUrl(\Omnyfy\Vendor\Block\Vendor\Location $subject, $result, $vendor)
    {
        $vendorUrl = $this->helperUrl->getVendorUrl($vendor);
        return $vendorUrl ? $vendorUrl : $result;
    }
}