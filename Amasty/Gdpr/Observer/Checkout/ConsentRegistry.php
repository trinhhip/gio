<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Observer\Checkout;

class ConsentRegistry
{
    protected $consents = [];

    public function setConsents(array $consents): void
    {
        $this->consents = $consents;
    }

    public function getConsents(): array
    {
        return $this->consents;
    }

    public function resetConsents(): void
    {
        $this->consents = [];
    }
}
