<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Ui\DataProvider\VisitHistory;

use Amasty\AdminActionsLog\Model\VisitHistoryEntry\ResourceModel\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
