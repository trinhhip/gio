<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession;
use Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel\ActiveSession as ActiveSessionResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpdateSchemaTo201
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $this->addAdminSessionInfoIdColumn($setup);
    }

    private function addAdminSessionInfoIdColumn(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(ActiveSessionResource::TABLE_NAME);
        $setup->getConnection()->addColumn(
            $table,
            ActiveSession::ADMIN_SESSION_INFO_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'default' => null,
                'comment' => 'Admin Session Info Id'
            ]
        );
    }
}
