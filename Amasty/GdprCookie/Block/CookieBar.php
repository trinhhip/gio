<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


declare(strict_types=1);

namespace Amasty\GdprCookie\Block;

use Amasty\GdprCookie\Model\ConfigProvider;
use Magento\Cms\Model\Template\Filter as CmsTemplateFilter;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

class CookieBar extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GdprCookie::cookiebar.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var CmsTemplateFilter
     */
    private $cmsTemplateFilter;

    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context,
        Json $jsonSerializer,
        CmsTemplateFilter $cmsTemplateFilter,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->cmsTemplateFilter = $cmsTemplateFilter;
    }

    /**
     * @return int
     */
    public function isProcessFirstShow()
    {
        return $this->configProvider->getFirstVisitShow();
    }

    /**
     * @return string
     */
    public function getNotificationText()
    {
        $text = $this->cmsTemplateFilter->filter($this->configProvider->getNotificationText());

        return $this->jsonSerializer->serialize($text);
    }

    /**
     * @return string
     */
    public function getAllowLink()
    {
        return $this->_urlBuilder->getUrl('gdprcookie/cookie/allow');
    }

    /**
     * @return int
     */
    public function getNoticeType()
    {
        return (int)$this->configProvider->getCookiePrivacyBarType();
    }

    /**
     * @return null|string
     */
    public function getPolicyTextColor()
    {
        return $this->configProvider->getPolicyTextColor();
    }

    /**
     * @return null|string
     */
    public function getBackgroundColor()
    {
        return $this->configProvider->getBackgroundColor();
    }

    /**
     * @return null|string
     */
    public function getAcceptButtonColor()
    {
        return $this->configProvider->getAcceptButtonColor();
    }

    /**
     * @return null|string
     */
    public function getAcceptButtonColorHover()
    {
        return $this->configProvider->getAcceptButtonColorHover();
    }

    /**
     * @return null|string
     */
    public function getAcceptTextColor()
    {
        return $this->configProvider->getAcceptTextColor();
    }

    /**
     * @return null|string
     */
    public function getAcceptTextColorHover()
    {
        return $this->configProvider->getAcceptTextColorHover();
    }

    /**
     * @return null|string
     */
    public function getAcceptButtonName()
    {
        return $this->configProvider->getAcceptButtonName();
    }

    /**
     * @return null|string
     */
    public function getLinksColor()
    {
        return $this->configProvider->getLinksColor();
    }

    /**
     * @return null|string
     */
    public function getBarLocation()
    {
        return $this->configProvider->getBarLocation();
    }

    /**
     * @return null|string
     */
    public function getSettingsButtonColor()
    {
        return $this->configProvider->getSettingsButtonColor();
    }

    /**
     * @return null|string
     */
    public function getSettingsButtonColorHover()
    {
        return $this->configProvider->getSettingsButtonColorHover();
    }

    /**
     * @return null|string
     */
    public function getSettingsTextColor()
    {
        return $this->configProvider->getSettingsTextColor();
    }

    /**
     * @return null|string
     */
    public function getSettingsTextColorHover()
    {
        return $this->configProvider->getSettingsTextColorHover();
    }

    /**
     * @return null|string
     */
    public function getSettingsButtonName()
    {
        return $this->configProvider->getSettingsButtonName();
    }

    /**
     * @return null|string
     */
    public function getTitleTextColor()
    {
        return $this->configProvider->getTitleTextColor();
    }

    /**
     * @return null|string
     */
    public function getDescriptionTextColor()
    {
        return $this->configProvider->getDescriptionTextColor();
    }

    /**
     * @return null|string
     */
    public function getDeclineEnabled()
    {
        return (int)$this->configProvider->getDeclineEnabled();
    }

    /**
     * @return null|string
     */
    public function getDeclineButtonColor()
    {
        return $this->configProvider->getDeclineButtonColor();
    }

    /**
     * @return null|string
     */
    public function getDeclineButtonColorHover()
    {
        return $this->configProvider->getDeclineButtonColorHover();
    }

    /**
     * @return null|string
     */
    public function getDeclineTextColor()
    {
        return $this->configProvider->getDeclineTextColor();
    }

    /**
     * @return null|string
     */
    public function getDeclineTextColorHover()
    {
        return $this->configProvider->getDeclineTextColorHover();
    }

    /**
     * @return null|string
     */
    public function getDeclineButtonName()
    {
        return $this->configProvider->getDeclineButtonName();
    }
}
