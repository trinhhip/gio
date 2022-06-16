<?php
namespace Omnyfy\VendorAuth\Model;

use Omnyfy\VendorAuth\Api\Data\TokenInterface;

class Token extends \Magento\Framework\Model\AbstractModel implements TokenInterface
{
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }
}
