<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\LogEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\LogEntry\LogDetail as LogDetailModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LogDetail extends AbstractDb
{
    const TABLE_NAME = 'amasty_audit_log_details';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, LogDetailModel::ID);
    }
}
