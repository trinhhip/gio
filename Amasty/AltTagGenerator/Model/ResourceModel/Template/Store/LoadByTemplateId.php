<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\ResourceModel\Template\Store;

use Amasty\AltTagGenerator\Model\ResourceModel\Template\Store\Table as StoreTable;
use Magento\Framework\App\ResourceConnection;

class LoadByTemplateId
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $templateId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $this->resourceConnection->getTableName(StoreTable::NAME),
            [StoreTable::STORE_COLUMN]
        )->where(sprintf('%s = ?', StoreTable::TEMPLATE_COLUMN), $templateId);

        return $connection->fetchCol($select);
    }
}
