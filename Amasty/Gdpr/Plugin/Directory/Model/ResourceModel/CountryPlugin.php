<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Plugin\Directory\Model\ResourceModel;

use Amasty\Gdpr\Model\Anonymizer;
use Amasty\Gdpr\Model\Config;
use Magento\Directory\Model\ResourceModel\Country;

/**
 * Plugin to allow loading of anonymized customer address
 * because country and region code is changed
 */
class CountryPlugin
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
     * @param Country $subject
     * @param \Closure $proceed
     * @param \Magento\Directory\Model\Country $country
     * @param string $code
     *
     * @return \Magento\Directory\Model\Country|mixed
     */
    public function aroundLoadByCode(
        Country $subject,
        \Closure $proceed,
        \Magento\Directory\Model\Country $country,
        $code
    ) {
        if ($this->config->isModuleEnabled()
            && ($this->config->isAllowed(Config::ANONYMIZE)
                || $this->config->isAllowed(Config::DELETE))
            && $code === Anonymizer::ANONYMIZE_COUNTRY_ID
        ) {
            $country->setName('anonymous');
            $country->setNameDefault('anonymous');

            return $country;
        }

        return $proceed($country, $code);
    }
}
