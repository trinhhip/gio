<?php

namespace OmnyfyCustomzation\Easyship\Plugin\Omnyfy\Easyship\Model\Carrier;

use Omnyfy\Easyship\Model\Carrier\EasyShipping as BaseEasyShipping;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class EasyShipping
 */
class EasyShipping
{
    public function aroundCollectRates(BaseEasyShipping $subject, $proceed, RateRequest $request)
    {
        $result = $proceed($request);
        // hide the message error
        if ($request->getDestCountryId() !== 'SG'
            && $result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error
        ) {
            return false;
        }

        return $result;
    }
}
