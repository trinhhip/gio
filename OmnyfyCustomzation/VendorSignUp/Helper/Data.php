<?php

namespace OmnyfyCustomzation\VendorSignUp\Helper;

/**
 * Class Data
 *
 * @package OmnyfyCustomzation\VendorSignUp\Helper
 */
class Data extends \Omnyfy\VendorSignUp\Helper\Data
{
    /**
     * @return mixed
     */
    public function getGoogleAddressDisabled (){
        return $this->scopeConfig->getValue(
            'omnyfy_vendorsignup/google_place/autocomplete_disabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}