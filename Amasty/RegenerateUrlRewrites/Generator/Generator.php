<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Generator;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterfaceFactory;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterfaceFactory;
use Amasty\RegenerateUrlRewrites\Api\GeneratorInterface;
use Amasty\RegenerateUrlRewrites\Generator\Command\CommandResultInterface;
use Amasty\RegenerateUrlRewrites\Generator\Command\CommandRunner;
use Amasty\RegenerateUrlRewrites\Generator\Generate\Status\Message;
use Amasty\RegenerateUrlRewrites\Generator\Processing\JobManager;
use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\GenerateProcess;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Generator implements GeneratorInterface
{
    /**
     * @var CommandRunner
     */
    private $commandRunner;

    /**
     * @var JobManager
     */
    private $jobManager;

    /**
     * @var GenerateStartResultInterfaceFactory
     */
    private $generateStartResultFactory;

    /**
     * @var GenerateStatusInterfaceFactory
     */
    private $generateStatusFactory;

    public function __construct(
        CommandRunner $commandRunner,
        JobManager $jobManager,
        GenerateStartResultInterfaceFactory $generateStartResultFactory,
        GenerateStatusInterfaceFactory $generateStatusFactory
    ) {
        $this->commandRunner = $commandRunner;
        $this->jobManager = $jobManager;
        $this->generateStartResultFactory = $generateStartResultFactory;
        $this->generateStatusFactory = $generateStatusFactory;
    }

    /**
     * @param GenerateConfigInterface $config
     * @return GenerateStartResultInterface
     */
    public function start(GenerateConfigInterface $config): GenerateStartResultInterface
    {
        /** @var GenerateStartResultInterface $result */
        $result = $this->generateStartResultFactory->create();
        try {
            $result->setProcessIdentity(
                $this->commandRunner->run($config)
            );
        } catch (LocalizedException $e) {
            $result->setError([
                Message::TYPE => CommandResultInterface::MESSAGE_CRITICAL,
                Message::MESSAGE => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * @param string|null $processIdentity
     * @return GenerateStatusInterface
     */
    public function getStatus(?string $processIdentity): GenerateStatusInterface
    {
        /** @var GenerateStatusInterface $result */
        $result = $this->generateStatusFactory->create();
        $result->setPid(null);
        if ($processIdentity) {
            try {
                /** @var GenerateProcess $process */
                $process = $this->jobManager->watchJob($processIdentity)->getJobState();
            } catch (NoSuchEntityException $e) {
                $result->setError(
                    [
                        Message::TYPE => CommandResultInterface::MESSAGE_CRITICAL,
                        Message::MESSAGE => (string)__('Process Identity is invalid.')
                    ]
                );

                return $result;
            } catch (LocalizedException $e) {
                $result->setError(
                    [
                        Message::TYPE => CommandResultInterface::MESSAGE_CRITICAL,
                        Message::MESSAGE => $e->getMessage()
                    ]
                );

                return $result;
            }

            if ($pid = $process->getPid()) {
                $result->setPid((int)$pid);
            }

            $commandResult = $process->getCommandResult();
            if ($commandResult === null) {
                $message = [
                    Message::TYPE => CommandResultInterface::MESSAGE_INFO,
                    Message::MESSAGE => (string)__('Process Started')
                ];

                $result->setStatus(GenerateProcess::STATUS_STARTING);
                $result->setProceed(0);
                $result->setTotal(0);
                $result->setMessages([$message]);
            } else {
                $result->setStatus($process->getStatus());
                $result->setProceed($commandResult->getRecordsProcessed());
                $result->setTotal($commandResult->getTotalRecords());
                $result->setMessages($commandResult->getMessages());
            }
        } else {
            $result->setError(
                [
                    Message::TYPE => CommandResultInterface::MESSAGE_CRITICAL,
                    Message::MESSAGE => (string)__('Process Identity is not set.')
                ]
            );
        }

        return $result;
    }

    public function terminate(string $processIdentity): bool
    {
        if (!$this->jobManager->terminateJob($processIdentity)) {
            throw new LocalizedException(__('Unable to terminate regeneration process.'));
        }

        return true;
    }
}
