<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Consent;

/**
 * Checkbox for customer account privacy settings page
 */
class AccountCheckbox extends Checkbox
{
    public function isChecked(Consent\Consent $consent): bool
    {
        return $this->dataProvider->haveAgreement($consent);
    }

    public function isRequired(Consent\Consent $consent): bool
    {
        return false;
    }
}
