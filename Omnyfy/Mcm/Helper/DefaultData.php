<?php

namespace Omnyfy\Mcm\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Data
 * @package Omnyfy\Mcm\Helper
 */
class DefaultData extends AbstractHelper {

    const XML_PATH = 'omnyfy_mcm/';
    const DEFAULT_VENDOR_TAX_NAME = 'set_default_fees/default_vendor_tax_name';
    const DEFAULT_VENDOR_TAX_RATE = 'set_default_fees/default_vendor_tax_rate';

    public function getConfigValue($field, $storeId = null) {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    public function getGeneralConfig($code, $storeId = null) {
        return $this->getConfigValue(self::XML_PATH . $code, $storeId);
    }

}
