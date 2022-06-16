<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;

class CleaningDate
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Config $config,
        DateTime $dateTime
    ) {
        $this->config = $config;
        $this->dateTime = $dateTime;
    }

    /**
     * @return string
     */
    public function getPersonalDataStoredDate()
    {
        if (!$this->config->isPersonalDataStored()) {
            return false;
        }
        $days = $this->config->getPersonalDataStoredDays();
        $time = '-' . $days . ' days';

        return $this->dateTime->gmtDate('Y-m-d H:i:s', strtotime($time));
    }

    /**
     * @return string
     */
    public function getPersonalDataDeletionDate()
    {
        if (!$this->config->isPersonalDataDeletion()) {
            return false;
        }
        $days = $this->config->getPersonalDataDeletionDays();
        $time = '-' . $days . ' days';

        return $this->dateTime->gmtDate('Y-m-d H:i:s', strtotime($time));
    }

    /**
     * @return string
     */
    public function getAutoCleaningDate()
    {
        if (!$this->config->isAutoCleaning()) {
            return false;
        }
        $days = $this->config->getAutoCleaningDays();
        $time = '-' . $days . ' days';

        return $this->dateTime->gmtDate('Y-m-d H:i:s', strtotime($time));
    }
}
