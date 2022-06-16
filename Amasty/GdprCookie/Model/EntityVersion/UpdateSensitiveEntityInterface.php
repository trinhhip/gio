<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\EntityVersion;

interface UpdateSensitiveEntityInterface
{
    /**
     * @return array
     */
    public function getSensitiveData(): array;
}
