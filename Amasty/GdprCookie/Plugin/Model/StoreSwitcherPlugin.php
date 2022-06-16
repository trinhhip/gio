<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


declare(strict_types=1);

namespace Amasty\GdprCookie\Plugin\Model;

use Amasty\GdprCookie\Model\CookieManager;
use Magento\Store\Model\StoreSwitcherInterface;

class StoreSwitcherPlugin
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var bool
     */
    private $isProcessed = false;

    public function __construct(
        CookieManager $cookieManager
    ) {
        $this->cookieManager = $cookieManager;
    }

    public function afterSwitch(StoreSwitcherInterface $subject, string $result): string
    {
        if ($this->isProcessed) {
            return $result;
        }

        $this->isProcessed = true;

        if ($groups = $this->cookieManager->getAllowCookies()) {
            $this->cookieManager->updateAllowedCookies($groups);
        }

        return $result;
    }
}
