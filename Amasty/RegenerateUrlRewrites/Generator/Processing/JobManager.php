<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Generator\Processing;

use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\GenerateProcess;
use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\GenerateProcessRepository;
use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\ResourceModel\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;

class JobManager
{
    const SIGTERM_CODE = 15;

    /**
     * @var CollectionFactory
     */
    private $generateProcessCollectionFactory;

    /**
     * @var JobWatcherFactory
     */
    private $jobWatcherFactory;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var GenerateProcessRepository
     */
    private $generateProcessRepository;

    /**
     * @var PhpExecutableFinder
     */
    private $phpExecutableFinder;

    public function __construct(
        CollectionFactory $generateProcessCollectionFactory,
        JobWatcherFactory $jobWatcherFactory,
        GenerateProcessRepository $generateProcessRepository,
        PhpExecutableFinder $phpExecutableFinder,
        Shell $shell
    ) {
        $this->generateProcessCollectionFactory = $generateProcessCollectionFactory;
        $this->jobWatcherFactory = $jobWatcherFactory;
        $this->shell = $shell;
        $this->generateProcessRepository = $generateProcessRepository;
        $this->phpExecutableFinder = $phpExecutableFinder;
    }

    /**
     * @param string $command
     * @param string $arguments
     * @param string|null $identity
     * @return JobWatcher
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestJob(string $command, string $arguments, string $identity = null): JobWatcher
    {
        $processId = null;
        try {
            $matchingProcess = $this->generateProcessRepository->getByIdentity($identity);

            if ($matchingProcess->getPid() && $this->isPidAlive((int)$matchingProcess->getPid())) {
                return $this->jobWatcherFactory->create([
                    'processIdentity' => $identity
                ]);
            } else {
                $processId = (int)$matchingProcess->getId();
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
            // Do nothing
        }

        $this->generateProcessRepository->initiateProcess($identity, $processId);
        $phpPath = $this->phpExecutableFinder->find() ?: 'php';

        $this->shell->execute(
            $phpPath . ' %s ' . $command . ' ' . $arguments . ' > /dev/null &',
            [
                BP . '/bin/magento'
            ]
        );

        return $this->jobWatcherFactory->create(['processIdentity' => $identity]);
    }

    /**
     * @param string $identity
     * @return JobWatcher
     * @throws NoSuchEntityException
     */
    public function watchJob(string $identity): JobWatcher
    {
        $matchingProcess = $this->generateProcessRepository->getByIdentity($identity);

        return $this->jobWatcherFactory->create([
            'processIdentity' => $matchingProcess->getIdentity(),
            'pid'             => (int)$matchingProcess->getPid()
        ]);
    }

    /**
     * @param int $pid
     * @return bool
     */
    public function isPidAlive(int $pid): bool
    {
        //phpcs:ignore
        return false !== posix_getpgid($pid);
    }

    public function terminateJob(string $identity): bool
    {
        $process = $this->generateProcessRepository->getByIdentity($identity);
        $pid = (int)$process->getPid();

        if ($pid && $this->isPidAlive($pid)) {
            $process->setStatus(GenerateProcess::STATUS_FAILED);
            $this->generateProcessRepository->updateProcess($process, false, false);

            //phpcs:ignore
            return posix_kill($pid, self::SIGTERM_CODE);
        }

        return true;
    }
}
