<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Plugin\Customer\Model\Address;

use Amasty\Gdpr\Model\Anonymizer;
use Amasty\Gdpr\Model\Config;
use Magento\Customer\Model\Address\AbstractAddress;

/**
 * Plugin for country and region anonymization
 * by default Magento doesn't allow to set random values
 * to the region and country
 */
class AbstractAddressPlugin
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Ignore validation if address is being anonymized
     *
     * @param AbstractAddress $subject
     */
    public function beforeValidate(AbstractAddress $subject)
    {
        if ($this->config->isModuleEnabled()
            && ($this->config->isAllowed(Config::ANONYMIZE)
                || $this->config->isAllowed(Config::DELETE))
            && $subject->getRegionId() === Anonymizer::ANONYMIZE_REGION_ID
            && $subject->getCountryId() === Anonymizer::ANONYMIZE_COUNTRY_ID
        ) {
            $subject->setData('should_ignore_validation', true);
        }
    }
}
