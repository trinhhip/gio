<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\VisitorConsentLog;

use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog as VisitorConsentLogResource;
use Magento\Framework\Model\AbstractModel;

class VisitorConsentLog extends AbstractModel
{
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const SESSION_ID = 'session_id';
    const DATE_CONSENTED = 'date_consented';
    const POLICY_VERSION = 'policy_version';
    const WEBSITE_ID = 'website_id';
    const IP = 'ip';

    public function _construct()
    {
        parent::_construct();

        $this->_init(VisitorConsentLogResource::class);
        $this->setIdFieldName(self::ID);
    }
}
