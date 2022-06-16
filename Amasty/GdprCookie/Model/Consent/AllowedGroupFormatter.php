<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


declare(strict_types=1);

namespace Amasty\GdprCookie\Model\Consent;

use Amasty\GdprCookie\Api\CookieManagementInterface;

class AllowedGroupFormatter
{
    const STATUS_FORMAT = '<strong>%s:</strong> Allowed<br/>';

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    public function __construct(CookieManagementInterface $cookieManagement)
    {
        $this->cookieManagement = $cookieManagement;
    }

    public function format(int $storeId, array $allowedGroupIds): string
    {
        $status = '';
        $groups = $this->cookieManagement->getGroups($storeId, $allowedGroupIds);

        foreach ($groups as $group) {
            $status .= sprintf(static::STATUS_FORMAT, $group->getName());
        }

        return rtrim($status, '<br/>');
    }
}
