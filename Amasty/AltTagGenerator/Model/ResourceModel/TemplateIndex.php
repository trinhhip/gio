<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class TemplateIndex
{
    const MAIN_TABLE = 'amasty_alt_template_index';
    const REPLICA_TABLE = 'amasty_alt_template_index_replica';

    const TEMPLATE_ID = 'template_id';
    const STORE_ID = 'store_id';
    const PRODUCT_ID = 'product_id';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }

    public function getTableName(string $tableName): string
    {
        return $this->resourceConnection->getTableName($tableName);
    }

    public function getAppliedTemplates(int $productId, int $storeId): array
    {
        $equalsTemplate = '%s = ?';

        $rulesSelect = $this->getConnection()->select()->from(
            $this->getTableName(static::MAIN_TABLE),
            [static::TEMPLATE_ID]
        )->where(
            sprintf($equalsTemplate, static::PRODUCT_ID),
            $productId
        )->where(
            sprintf($equalsTemplate, static::STORE_ID),
            $storeId
        );

        return $this->getConnection()->fetchCol($rulesSelect);
    }
}
