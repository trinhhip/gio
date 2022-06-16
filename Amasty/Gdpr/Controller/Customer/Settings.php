<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Customer;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent\DataProvider\PrivacySettingsDataProvider;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Customer\Controller\AbstractAccount as AbstractAccountAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Settings extends AbstractAccountAction
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var PrivacySettingsDataProvider
     */
    private $privacySettingsDataProvider;

    public function __construct(
        Context $context,
        Config $configProvider,
        PrivacySettingsDataProvider $privacySettingsDataProvider
    ) {
        parent::__construct($context);
        $this->configProvider = $configProvider;
        $this->privacySettingsDataProvider = $privacySettingsDataProvider;
    }

    public function execute()
    {
        if (!$this->configProvider->isModuleEnabled()
            || (!$this->configProvider->isAnySectionVisible()
                && !($this->configProvider->isAllowed(Config::CONSENT_OPTING)
                    //because consent opting is dynamic section need to check it
                    && !empty($this->privacySettingsDataProvider->getData(ConsentLogger::FROM_PRIVACY_SETTINGS))
                )
                && !$this->configProvider->isDisplayDpoInfo()
            )
        ) {
            return $this->_forward('noroute');
        }

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
