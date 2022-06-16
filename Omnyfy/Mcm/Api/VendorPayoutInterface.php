<?php

namespace Omnyfy\Mcm\Api;

interface VendorPayoutInterface
{
    public function getPayoutAmount($vendorId, $orderId);
}
