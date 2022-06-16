<?php


namespace OmnyfyCustomzation\Vendor\Plugin\Vendor;


use OmnyfyCustomzation\Vendor\Helper\Url;

class Brand
{
    /**
     * @var Url
     */
    protected $helperUrl;

    /**
     * Brand constructor.
     * @param Url $helperUrl
     */
    public function __construct(
        Url $helperUrl
    )
    {
        $this->helperUrl = $helperUrl;
    }

    public function afterGetVendorUrl(\Omnyfy\Vendor\Block\Vendor\Brand $subject, $result, $vendor)
    {
        $vendorUrl = $this->helperUrl->getVendorUrl($vendor);
        return $vendorUrl ? $vendorUrl : $result;
    }
}