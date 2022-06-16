<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model;

class ActionLoggerFromAdmin extends ActionLogger
{
    const ADMIN_OPTION_MAPPING = [
        'data_anonymised_by_customer' => 'data_anonymised_by_admin',
        'delete_request_approved' => 'data_deleted_by_admin',
    ];

    public function logAction($action, $customerId = null, $comment = null)
    {
        $action = self::ADMIN_OPTION_MAPPING[$action] ?? $action;

        return parent::logAction($action, $customerId, $comment);
    }
}
