<?php
namespace Omnyfy\VendorAuth\Api;

interface VendorTokenServiceInterface
{
    /**
     * Get access token for vendor given the admin credentials.
     *
     * @param string $username
     * @param string $password
     * @return \Omnyfy\VendorAuth\Api\Data\TokenInterface Token created
     */
    public function getVendorAccessToken($username, $password);
}
