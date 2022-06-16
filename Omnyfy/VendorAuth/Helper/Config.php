<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 23/03/2020
 * Time: 9:28 AM
 */

namespace Omnyfy\VendorAuth\Helper;

use Magento\Framework\App\Helper\Context;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const IS_VENDOR_CREATE_CUSTOMER = 'omnyfy_vendor/vendor/create_customer';

    public function isVendorCreateCustomer()
    {
        return $this->scopeConfig->getValue(
            self::IS_VENDOR_CREATE_CUSTOMER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
