<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const PATH_PREFIX = 'amasty_gdpr';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const PRIVACY_CHECKBOX_EEA_COUNTRIES = 'privacy_checkbox/eea_countries';

    const MODULE_ENABLED = 'general/enabled';
    const DISPLAY_PP_POPUP = 'general/display_pp_popup';
    const LOG_GUEST = 'general/log_guest';
    const EU_COUNTRIES = 'general/country/eu_countries';
    const AUTO_CLEANING = 'general/auto_cleaning';
    const AUTO_CLEANING_DAYS = 'general/auto_cleaning_days';
    const AVOID_ANONYMIZATION = 'general/avoid_anonymisation';
    const ORDER_STATUSES = 'general/order_statuses';
    const AVOID_GIFT_REGISTRY_ANONYMIZATION = 'general/gift_registry_anonymisation';

    const NOTIFICATE_ADMIN = 'deletion_notification/enable_admin_notification';
    const NOTIFICATE_ADMIN_TEMPLATE = 'deletion_notification/admin_template';
    const NOTIFICATE_ADMIN_SENDER = 'deletion_notification/admin_sender';
    const NOTIFICATE_ADMIN_RECIEVER = 'deletion_notification/admin_reciever';

    const EMAIL_NOTIFICATION_TEMPLATE = '_notification/template';
    const EMAIL_NOTIFICATION_SENDER = '_notification/sender';
    const EMAIL_NOTIFICATION_REPLY_TO = '_notification/reply_to';

    const ALLOWED = 'customer_access_control/';
    const DOWNLOAD = 'download';
    const ANONYMIZE = 'anonymize';
    const DELETE = 'delete';
    const GIVEN_CONSENTS = 'given_consents';
    const CONSENT_OPTING = 'consent_opting';
    const SKIP_EMPTY_FIELDS = 'customer_access_control/skip_empty_fields';
    const DISPLAY_DPO_INFO = 'customer_access_control/display_dpo_info';
    const DPO_SECTION_NAME = 'customer_access_control/dpo_section_name';
    const DPO_INFO = 'customer_access_control/dpo_info';

    const PERSONAL_DATA_DELETION = 'personal_data/automatic_personal_data_deletion/personal_data_deletion';
    const PERSONAL_DATA_DELETION_DAYS = 'personal_data/automatic_personal_data_deletion/personal_data_deletion_days';
    const PERSONAL_DATA_STORED = 'personal_data/anonymization_data/personal_data_stored';
    const PERSONAL_DATA_STORED_DAYS = 'personal_data/anonymization_data/personal_data_stored_days';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
    }

    /**
     * @return array
     */
    public function getEEACountryCodes()
    {
        $codes = explode(',', $this->getValue(self::PRIVACY_CHECKBOX_EEA_COUNTRIES));

        return $codes;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * @param string $path
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return bool
     */
    public function isSetFlag($path, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::PATH_PREFIX . '/' . $path, $scopeType, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function isAdminDeleteNotificationEnabled($scopeCode = null)
    {
        return (bool)$this->getValue(self::NOTIFICATE_ADMIN, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAdminNotificationTemplate($scopeCode = null)
    {
        return $this->getValue(self::NOTIFICATE_ADMIN_TEMPLATE, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAdminNotificationSender($scopeCode = null)
    {
        return $this->getValue(self::NOTIFICATE_ADMIN_SENDER, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAdminNotificationReciever($scopeCode = null)
    {
        return $this->getValue(self::NOTIFICATE_ADMIN_RECIEVER, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return string|null
     */
    public function isLogGuest($scopeCode = null)
    {
        return $this->getValue(self::LOG_GUEST, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @return bool
     */
    public function isAutoCleaning()
    {
        return $this->isSetFlag(self::AUTO_CLEANING);
    }

    /**
     * @return int
     */
    public function getAutoCleaningDays()
    {
        return (int)$this->getValue(self::AUTO_CLEANING_DAYS);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isAvoidAnonymization($scopeCode = null)
    {
        return $this->isSetFlag(self::AVOID_ANONYMIZATION, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getOrderStatuses($scopeCode = null)
    {
        return $this->getValue(self::ORDER_STATUSES, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null $scopeCode
     *
     * @return string|null
     */
    public function isAvoidGiftRegistryAnonymization($scopeCode = null)
    {
        return $this->getValue(
            self::AVOID_GIFT_REGISTRY_ANONYMIZATION,
            $scopeCode,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @param string      $configPath
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getConfirmationEmailTemplate($configPath, $scopeCode = null)
    {
        return $this->getValue(
            $configPath . self::EMAIL_NOTIFICATION_TEMPLATE,
            $scopeCode,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string      $configPath
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getConfirmationEmailSender($configPath, $scopeCode = null)
    {
        return $this->getValue($configPath . self::EMAIL_NOTIFICATION_SENDER, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string      $configPath
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getConfirmationEmailReplyTo($configPath, $scopeCode = null)
    {
        return $this->getValue(
            $configPath . self::EMAIL_NOTIFICATION_REPLY_TO,
            $scopeCode,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string      $configPath
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isAllowed($configPath, $scopeCode = null)
    {
        return $this->isSetFlag(self::ALLOWED . $configPath, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    public function isSkipEmptyFields($scopeCode = null): bool
    {
        return $this->isSetFlag(self::SKIP_EMPTY_FIELDS, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isDisplayDpoInfo($scopeCode = null)
    {
        return $this->isSetFlag(self::DISPLAY_DPO_INFO, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param null $scopeCode
     *
     * @return string|null
     */
    public function getDpoSectionName($scopeCode = null)
    {
        return $this->getValue(
            self::DPO_SECTION_NAME,
            $scopeCode,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null $scopeCode
     *
     * @return string|null
     */
    public function getDpoInfo($scopeCode = null)
    {
        return $this->getValue(
            self::DPO_INFO,
            $scopeCode,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isModuleEnabled($scopeCode = null)
    {
        return $this->isSetFlag(self::MODULE_ENABLED, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isDisplayPpPopup($scopeCode = null)
    {
        return $this->isSetFlag(self::DISPLAY_PP_POPUP, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getEuCountriesCodes()
    {
        $countriesCodes = $this->scopeConfig->getValue(self::EU_COUNTRIES) ?: '';

        return explode(',', $countriesCodes);
    }

    /**
     * @return bool
     */
    public function isPersonalDataDeletion()
    {
        return $this->isSetFlag(self::PERSONAL_DATA_DELETION);
    }

    /**
     * @return int
     */
    public function getPersonalDataDeletionDays()
    {
        return (int)$this->getValue(self::PERSONAL_DATA_DELETION_DAYS);
    }

    /**
     * @return bool
     */
    public function isPersonalDataStored()
    {
        return $this->isSetFlag(self::PERSONAL_DATA_STORED);
    }

    /**
     * @return int
     */
    public function getPersonalDataStoredDays()
    {
        return (int)$this->getValue(self::PERSONAL_DATA_STORED_DAYS);
    }

    /**
     * @return bool
     */
    public function isAnySectionVisible()
    {
        return $this->isAllowed(self::DOWNLOAD)
            || $this->isAllowed(self::ANONYMIZE)
            || $this->isAllowed(self::DELETE);
    }
}
