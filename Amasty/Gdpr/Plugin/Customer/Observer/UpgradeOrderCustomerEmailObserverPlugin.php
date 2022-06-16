<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Plugin\Customer\Observer;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\FlagRegistry;
use Magento\Customer\Observer\UpgradeOrderCustomerEmailObserver;
use Magento\Framework\Event\Observer;

/**
 * This plugin disables updating orders customer email after
 * corresponding customer email changed
 */
class UpgradeOrderCustomerEmailObserverPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var FlagRegistry
     */
    private $flagRegistry;

    public function __construct(
        Config $config,
        FlagRegistry $flagRegistry
    ) {
        $this->config = $config;
        $this->flagRegistry = $flagRegistry;
    }

    public function aroundExecute(
        UpgradeOrderCustomerEmailObserver $subject,
        \Closure $proceed,
        Observer $observer
    ): void {
        if (!$this->config->isModuleEnabled()
            || !$this->flagRegistry->getUpgradeOrderCustomerEmailDisabledFlag()
        ) {
            $proceed($observer);
        }
    }
}
