<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 14/09/2020
 * Time: 2:38 PM
 */

namespace Omnyfy\VendorAuth\Plugin\Magento\Integration\Model;


class AdminTokenService
{
    private $_vendorHelper;

    public function __construct(
        \Omnyfy\VendorAuth\Helper\Vendor $vendorHelper
    )
    {
        $this->_vendorHelper = $vendorHelper;
    }

    public function beforeCreateAdminAccessToken(
        \Magento\Integration\Model\AdminTokenService $subject,
        $username,
        $password
    ) {
        $user = $this->_vendorHelper->getUserId($username);
        if ($user) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('***********');
            $vendor = $this->_vendorHelper->getVendorIdFromUserId($user->getId());
            if ($vendor){
                throw new \Magento\Framework\Exception\AuthenticationException(
                    __('You are not autherized to create a token.')
                );
            }
        }

        return [$username, $password];
    }
}