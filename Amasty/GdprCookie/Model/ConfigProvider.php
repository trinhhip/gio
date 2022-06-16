<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBarStyle;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    /**#@+
     * Constants defined for xpath of system configuration
     */
    const COOKIE_POLICY_BAR = 'cookie_policy/bar';

    const COOKIE_POLICY_BAR_TYPE = 'cookie_bar_customisation/cookies_bar_style';

    const FIRST_VISIT_SHOW = 'cookie_policy/first_visit_show';

    const COOKIE_POLICY_BAR_VISIBILITY = 'cookie_policy/bar_visibility';

    const COOKIE_POLICY_BAR_COUNTRIES = 'cookie_policy/bar_countries';

    const EU_COUNTRIES = 'general/country/eu_countries';

    const AUTO_CLEAR_LOG_DAYS = 'cookie_policy/auto_cleaning_days';

    const COOKIE_BAR_LOCATION = 'cookie_bar_customisation/classic_bar/cookies_bar_location';

    const SIDEBAR_GROUP_TITLE_TEXT_COLOR = 'cookie_bar_customisation/sidebar/group_title_text_color';

    const SIDEBAR_GROUP_DESCRIPTION_TEXT_COLOR = 'cookie_bar_customisation/sidebar/group_desc_text_color';

    const TYPE_POPUP = 'cookie_bar_customisation/popup/';

    const TYPE_SIDEBAR = 'cookie_bar_customisation/sidebar/';

    const TYPE_CLASSIC = 'cookie_bar_customisation/classic_bar/';

    const NOTIFICATION_TEXT = 'notification_text';

    const BACKGROUND_COLOR = 'background_color';

    const LINKS_COLOR = 'links_color';

    const POLICY_TEXT_COLOR = 'policy_text_color';

    const ACCEPT_BUTTON_TEXT = 'accept_button/button_text';

    const ACCEPT_BUTTON_COLOR = 'accept_button/button_color';

    const ACCEPT_BUTTON_COLOR_HOVER = 'accept_button/button_color_hover';

    const ACCEPT_TEXT_COLOR = 'accept_button/text_color';

    const ACCEPT_TEXT_COLOR_HOVER = 'accept_button/text_color_hover';

    const SETTINGS_BUTTON_TEXT = 'settings_button/button_text';

    const SETTINGS_BUTTON_COLOR = 'settings_button/button_color';

    const SETTINGS_BUTTON_COLOR_HOVER = 'settings_button/button_color_hover';

    const SETTINGS_TEXT_COLOR = 'settings_button/text_color';

    const SETTINGS_TEXT_COLOR_HOVER = 'settings_button/text_color_hover';

    const DECLINE_BUTTON_TEXT = 'decline_button/button_text';

    const DECLINE_BUTTON_COLOR = 'decline_button/button_color';

    const DECLINE_BUTTON_COLOR_HOVER = 'decline_button/button_color_hover';

    const DECLINE_TEXT_COLOR = 'decline_button/text_color';

    const DECLINE_TEXT_COLOR_HOVER = 'decline_button/text_color_hover';

    const DECLINE_ENABLE = 'decline_button/enable';

    /**#@-*/

    protected $pathPrefix = 'amasty_gdprcookie/';

    /**
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isCookieBarEnabled($scopeCode = null)
    {
        return (bool)$this->getValue(self::COOKIE_POLICY_BAR, $scopeCode);
    }

    /**
     * @param null $scopeCode
     * @return int
     */
    public function getCookiePrivacyBarType($scopeCode = null)
    {
        return (int)$this->getValue(self::COOKIE_POLICY_BAR_TYPE, $scopeCode);
    }

    /**
     * @param null|int $scopeCode
     *
     * @return int
     */
    public function getFirstVisitShow($scopeCode = null)
    {
        return (int)$this->getValue(self::FIRST_VISIT_SHOW, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return string
     */
    public function getCookiePolicyBarVisibility($scopeCode = null)
    {
        return (int)$this->getValue(self::COOKIE_POLICY_BAR_VISIBILITY, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return array
     */
    public function getCookiePolicyBarCountriesCodes($scopeCode = null)
    {
        $countriesCodes = (string)$this->getValue(self::COOKIE_POLICY_BAR_COUNTRIES, $scopeCode);

        return array_filter(explode(',', $countriesCodes));
    }

    /**
     * @return array
     */
    public function getEuCountriesCodes()
    {
        $countriesCodes = (string)$this->scopeConfig->getValue(self::EU_COUNTRIES);

        return array_filter(explode(',', $countriesCodes));
    }

    /**
     * @param null|string $scopeCode
     *
     * @return string
     */
    public function getNotificationText($scopeCode = null)
    {
        return (string)$this->getValue($this->getCustomisationType() . self::NOTIFICATION_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getBackgroundColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::BACKGROUND_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getPolicyTextColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::POLICY_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getLinksColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::LINKS_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptButtonName($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_BUTTON_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptButtonColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_BUTTON_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptButtonColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_BUTTON_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptTextColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAcceptTextColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::ACCEPT_TEXT_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsButtonName($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_BUTTON_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsButtonColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_BUTTON_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsButtonColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_BUTTON_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsTextColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getSettingsTextColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::SETTINGS_TEXT_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|int $scopeCode
     *
     * @return int
     */
    public function getDeclineEnabled($scopeCode = null)
    {
        return (int)$this->getValue($this->getCustomisationType() . self::DECLINE_ENABLE, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineButtonName($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_BUTTON_TEXT, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineButtonColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_BUTTON_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineButtonColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_BUTTON_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineTextColor($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDeclineTextColorHover($scopeCode = null)
    {
        return $this->getValue($this->getCustomisationType() . self::DECLINE_TEXT_COLOR_HOVER, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getTitleTextColor($scopeCode = null)
    {
        return $this->getValue(self::SIDEBAR_GROUP_TITLE_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getDescriptionTextColor($scopeCode = null)
    {
        return $this->getValue(self::SIDEBAR_GROUP_DESCRIPTION_TEXT_COLOR, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getBarLocation($scopeCode = null)
    {
        return $this->getValue(self::COOKIE_BAR_LOCATION, $scopeCode);
    }

    /**
     * @param null|int $scopeCode
     *
     * @return int
     */
    public function getAutoCleaningDays($scopeCode = null)
    {
        return (int)$this->getValue(self::AUTO_CLEAR_LOG_DAYS, $scopeCode);
    }

    /**
     * @return string
     */
    public function getCustomisationType()
    {
        switch ($this->getCookiePrivacyBarType()) {
            case CookiePolicyBarStyle::CONFIRMATION:
                return self::TYPE_CLASSIC;
            case CookiePolicyBarStyle::CONFIRMATION_MODAL:
                return self::TYPE_SIDEBAR;
            case CookiePolicyBarStyle::CONFIRMATION_POPUP:
                return self::TYPE_POPUP;
            default:
                return '';
        }
    }
}
