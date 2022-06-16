<?php
namespace Omnyfy\VendorAuth\Api\Data;

interface TokenInterface
{
    const TOKEN = 'token';

    /**
     * Get route
     * @return string|null
     */
    public function getToken();

    /**
     * Set route
     * @param string $token
     * @return $this
     */
    public function setToken($token);

}
