<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\Cron;

use Amasty\GdprCookie\Model\ConfigProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Amasty\GdprCookie\Setup\Operation\CreateCookieConsentTable;

class ClearLog
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        ConfigProvider $configProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->configProvider = $configProvider;
        $this->dateTime = $dateTime;
    }

    public function clearLog()
    {
        $days = $this->configProvider->getAutoCleaningDays();
        $time = '-' . $days . ' days';
        $dateForRemove = $this->dateTime->gmtDate('Y-m-d H:i:s', strtotime($time));
        $tableName = $this->resourceConnection->getTableName(CreateCookieConsentTable::TABLE_NAME);
        $this->resourceConnection->getConnection()->delete(
            $tableName,
            ['date_recieved < ?' => $dateForRemove]
        );
    }
}
