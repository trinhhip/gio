<?php

namespace Amasty\RegenerateUrlRewrites\Api;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface;

interface GeneratorInterface
{
    /**
     * @param GenerateConfigInterface $config
     * @return GenerateStartResultInterface
     */
    public function start(GenerateConfigInterface $config): GenerateStartResultInterface;

    /**
     * @param string|null $processIdentity
     * @return GenerateStatusInterface
     */
    public function getStatus(?string $processIdentity): GenerateStatusInterface;

    /**
     * @param string $processIdentity
     * @return bool
     */
    public function terminate(string $processIdentity): bool;
}
