<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Api;

interface RequestInterface
{
    /**
     * @param int
     *
     * @return void
     */
    public function approveDeleteRequest($customerId);

    /**
     * @param int
     * @param string
     *
     * @return void
     */
    public function denyDeleteRequest($customerId, $comment);

    /**
     * @return string[]
     */
    public function getUnprocessedRequests();
}
