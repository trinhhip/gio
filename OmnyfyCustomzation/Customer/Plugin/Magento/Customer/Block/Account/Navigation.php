<?php

namespace OmnyfyCustomzation\Customer\Plugin\Magento\Customer\Block\Account;

/**
 * Class Navigation
 */
class Navigation
{
    /**
     * @param $subject
     * @param $result
     *
     * @return array
     */
    function afterGetLinks($subject, $result)
    {
        foreach ($result as $k => $link) {
            if (strpos($link->getHref(), 'rewards/account')
                || strpos($link->getHref(), 'vendorreview')
                || strpos($link->getHref(), 'paypal/billing_agreement')
                || strpos($link->getHref(), 'downloadable/customer/products')
                || strpos($link->getHref(), 'returns/rma/list')
                || strpos($link->getHref(), 'stripe/customer/subscriptions')
                || strpos($link->getHref(), 'shop/favourite/vendor')
            ) {
                unset($result[$k]);
            }
        }
        return $result;
    }
}
