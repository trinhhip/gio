<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Config;
use Magento\Framework\View\Element\Template;

class PolicyPopup extends Template
{
    protected $_template = 'policy_popup.phtml';

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        Config $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getTextUrl()
    {
        return $this->getUrl('gdpr/policy/policytext');
    }

    /**
     * @return string
     */
    public function getPopupDataUrl()
    {
        return $this->getUrl('gdpr/policy/popupData');
    }

    /**
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->getUrl('gdpr/policy/accept');
    }

    /**
     * @return bool
     */
    public function showOnPageLoad()
    {
        return ($this->configProvider->isModuleEnabled()
            && $this->configProvider->isDisplayPpPopup());
    }
}
