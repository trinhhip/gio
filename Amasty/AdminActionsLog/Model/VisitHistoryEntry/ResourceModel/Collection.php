<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\VisitHistoryEntry as VisitHistoryEntryResource;
use Amasty\AdminActionsLog\Model\VisitHistoryEntry\VisitHistoryEntry as VisitHistoryEntryModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(VisitHistoryEntryModel::class, VisitHistoryEntryResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
