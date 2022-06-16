<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Generator\Processing;

use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\GenerateProcess;
use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\GenerateProcessRepository;

class JobWatcher
{
    /**
     * @var int
     */
    protected $processIdentity;

    /**
     * @var GenerateProcessRepository
     */
    private $generateProcessRepository;

    public function __construct(
        GenerateProcessRepository $generateProcessRepository,
        string $processIdentity = null
    ) {
        $this->processIdentity = $processIdentity;
        $this->generateProcessRepository = $generateProcessRepository;
    }

    /**
     * @return GenerateProcess
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getJobState(): GenerateProcess
    {
        return $this->generateProcessRepository->getByIdentity($this->processIdentity);
    }
}
