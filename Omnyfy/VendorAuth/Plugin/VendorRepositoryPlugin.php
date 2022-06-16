<?php
namespace Omnyfy\VendorAuth\Plugin;
use Magento\Framework\Exception\AuthenticationException;

class VendorRepositoryPlugin
{
    /**
     * @var \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
     */
    protected $vendorApiHelper;

    /**
     * VendorRepositoryPlugin constructor
     * @param \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
     */

    public function __construct(
        \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
    ) {
        $this->vendorApiHelper = $vendorApiHelper;
    }

    public function beforeGetById(
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $subject,
        $vendorId
    ){

        $tokenVendorId = $this->vendorApiHelper->getVendorIdFromToken();
        if ($tokenVendorId > 0 && $vendorId != $tokenVendorId) {
            throw new AuthenticationException(__('User is not authorized'));
        }
        return [$vendorId];
    }
}
