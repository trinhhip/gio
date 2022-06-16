<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Model;

class FlagRegistry
{
    private const ENABLE_SESSION_PLUGIN = 'enable_session_plugin';
    private const UPGRADE_ORDER_CUSTOMER_EMAIL_DISABLED = 'upgrade_order_customer_email_disabled';

    private $flags = [];

    private function setFlag(string $flagName, $flagValue = null): void
    {
        if (!empty($flagName)) {
            $this->flags[$flagName] = $flagValue;
        }
    }

    private function getFlag(string $flagName)
    {
        return $this->flags[$flagName] ?? null;
    }

    public function setFlagEnableSessionPlugin(?bool $flagValue = null): void
    {
        $this->setFlag(self::ENABLE_SESSION_PLUGIN, $flagValue);
    }

    public function getFlagEnableSessionPlugin(): ?bool
    {
        return $this->getFlag(self::ENABLE_SESSION_PLUGIN);
    }

    public function setUpgradeOrderCustomerEmailDisabledFlag(?bool $flagValue = null): void
    {
        $this->setFlag(self::UPGRADE_ORDER_CUSTOMER_EMAIL_DISABLED, $flagValue);
    }

    public function getUpgradeOrderCustomerEmailDisabledFlag(): ?bool
    {
        return $this->getFlag(self::UPGRADE_ORDER_CUSTOMER_EMAIL_DISABLED);
    }
}
