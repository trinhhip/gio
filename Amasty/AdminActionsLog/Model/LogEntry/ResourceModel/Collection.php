<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\LogEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\LogEntry\LogEntry as LogEntryModel;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry as LogEntryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(LogEntryModel::class, LogEntryResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
