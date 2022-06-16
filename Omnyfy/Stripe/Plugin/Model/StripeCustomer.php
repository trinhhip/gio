<?php
namespace Omnyfy\Stripe\Plugin\Model;

class StripeCustomer
{
    public function afterGetSubscriptions(\StripeIntegration\Payments\Model\StripeCustomer $subject, $result, $params = null)
    {
        if(!$subject->getStripeId()){
            return [];
        } else {
            return $result;
        }
    }
}
