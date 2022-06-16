<?php

namespace OmnyfyCustomzation\Shipping\Plugin\Omnyfy\Order\Helper;

/**
 * Class Shipping
 */
class Shipping
{
    function afterGetTemplate($subject, $result)
    {
        if ($result == 'Magento_Shipping::order/view/info.phtml') {
            $result = 'OmnyfyCustomzation_Shipping::order/view/info.phtml';
        }
        return $result;
    }
}
