<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


declare(strict_types=1);

namespace Amasty\GdprCookie\Model\EntityVersion;

class UpdateDataChecker
{
    /**
     * This method tracks sensitive data changes in two entities.
     * In case of data changes, checkerCallback will executed with provided entities.
     *
     * checkerCallback MUST include "data changes ignore" logic to prevent tracking data changes on disabled entities.
     *
     * @param UpdateSensitiveEntityInterface $firstEntity
     * @param UpdateSensitiveEntityInterface $secondEntity
     * @param \Closure|null $checkerCallback
     * @return bool
     */
    public function execute(
        UpdateSensitiveEntityInterface $firstEntity,
        UpdateSensitiveEntityInterface $secondEntity,
        \Closure $checkerCallback = null
    ): bool {
        $isDataChanged = $firstEntity->getSensitiveData() != $secondEntity->getSensitiveData();
        if ($isDataChanged && $checkerCallback) {
            $isDataChanged = $checkerCallback($firstEntity, $secondEntity);
        }

        return (bool)$isDataChanged;
    }
}
