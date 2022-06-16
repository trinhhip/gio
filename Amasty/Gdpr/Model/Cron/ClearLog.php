<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\Cron;

use Amasty\Gdpr\Api\Data\ActionLogInterface;
use Amasty\Gdpr\Model\CleaningDate;
use Amasty\Gdpr\Setup\Operation\CreateActionLogTable;
use Magento\Framework\App\ResourceConnection;

class ClearLog
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CleaningDate
     */
    private $cleaningDate;

    public function __construct(
        ResourceConnection $resourceConnection,
        CleaningDate $cleaningDate
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->cleaningDate = $cleaningDate;
    }

    public function clearLog()
    {
        if (!$dateForRemove = $this->cleaningDate->getAutoCleaningDate()) {
            return;
        }
        $tableName = $this->resourceConnection->getTableName(CreateActionLogTable::TABLE_NAME);
        $this->resourceConnection->getConnection()->delete(
            $tableName,
            [ActionLogInterface::CREATED_AT . ' < ?' => $dateForRemove]
        );
    }
}
