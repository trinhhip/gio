<?php

declare(strict_types = 1);

namespace Amasty\RegenerateUrlRewrites\Generator\Command;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface;
use Amasty\RegenerateUrlRewrites\Generator\Generate\Argument\ArgumentResolver;
use Amasty\RegenerateUrlRewrites\Generator\Processing\JobManager;

class CommandRunner
{
    const COMMAND = 'amurlrewrites:regenerate';

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @var JobManager
     */
    private $jobManager;

    public function __construct(
        ArgumentResolver $argumentResolver,
        JobManager $jobManager
    ) {
        $this->argumentResolver = $argumentResolver;
        $this->jobManager = $jobManager;
    }

    /**
     * @param GenerateConfigInterface $config
     * @return string|null
     */
    public function run(GenerateConfigInterface $config): ?string
    {
        if (!$config->isIncludeToRegeneration()) {
            return null;
        }

        $processIdentity = $this->getProcessIdentity();
        $config->setProcessIdentity($processIdentity);
        $this->jobManager->requestJob(
            self::COMMAND,
            $this->argumentResolver->getArguments($config),
            $processIdentity
        );

        return $processIdentity;
    }

    /**
     * @return string
     */
    private function getProcessIdentity(): string
    {
        return 'console_command_regenerate';
    }
}
