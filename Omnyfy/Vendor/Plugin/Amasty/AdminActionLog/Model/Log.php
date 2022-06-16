<?php

namespace Omnyfy\Vendor\Plugin\Amasty\AdminActionLog\Model;

use Omnyfy\Vendor\Model\Resource\Vendor as VendorResource;

class Log
{

    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        VendorResource $vendorResource
    ) {
        $this->_authSession = $authSession;
        $this->vendorResource = $vendorResource;
    }

    public function afterPrepareLogData(\Amasty\AdminActionsLog\Model\Log $subject, $result)
    {
        if ($user = $this->_authSession->getUser()) {
            //get vendor id for this user
            $userId = $user->getId();

            // Check if the current user is a vendor
            $vendorId = $this->vendorResource->getVendorIdByUserId($userId);
            if (empty($vendorId)) {
                $result['vendor_id'] = 0;
            } else {
                $result['vendor_id'] = $vendorId;
            }
        }
        return $result;
    }
}
