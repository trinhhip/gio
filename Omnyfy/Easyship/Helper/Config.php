<?php
namespace Omnyfy\Easyship\Helper;

use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\ScopeInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_PLATFORM_NAME = 'carriers/easyship/platform_name';

    const CALCULATE_SHIPPING_BY = 'omnyfy_vendor/vendor/calculate_shipping_by';

    const OVERALL_PICKUP_LOCATION = 'omnyfy_vendor/vendor/overall_pickup_location';
    
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }
    
    public function getPlatformName()
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_PLATFORM_NAME, ScopeInterface::SCOPE_STORE);
    }

    public function getCalculateShippingBy()
    {
        return  $this->scopeConfig->getValue(self::CALCULATE_SHIPPING_BY, ScopeInterface::SCOPE_STORE);
    }

    public function getOverallPickupLocation()
    {
        return  $this->scopeConfig->getValue(self::OVERALL_PICKUP_LOCATION, ScopeInterface::SCOPE_STORE);
    }
}