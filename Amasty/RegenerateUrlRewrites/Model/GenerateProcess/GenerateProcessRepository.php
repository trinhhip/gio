<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Model\GenerateProcess;

use Amasty\RegenerateUrlRewrites\Generator\Command\CommandResultInterface;
use Amasty\RegenerateUrlRewrites\Generator\Command\CommandResultInterfaceFactory;
use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\ResourceModel\CollectionFactory;
use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\ResourceModel\GenerateProcess as GenerateProcessResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GenerateProcessRepository
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var GenerateProcessResource
     */
    private $generateProcessResource;

    /**
     * @var GenerateProcessFactory
     */
    private $generateProcessFactory;

    /**
     * @var CommandResultInterfaceFactory
     */
    private $commandResultFactory;

    private $generateProcesses = [];

    public function __construct(
        CollectionFactory $collectionFactory,
        GenerateProcessFactory $generateProcessFactory,
        GenerateProcessResource $generateProcessResource,
        CommandResultInterfaceFactory $commandResultFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->generateProcessResource = $generateProcessResource;
        $this->generateProcessFactory = $generateProcessFactory;
        $this->commandResultFactory = $commandResultFactory;
    }

    /**
     * @param $identity
     * @return GenerateProcess
     * @throws NoSuchEntityException
     */
    public function getByIdentity($identity): GenerateProcess
    {
        if (!isset($this->generateProcesses[$identity])) {
            /** @var GenerateProcess $generateProcess */
            $generateProcess = $this->generateProcessFactory->create();
            $this->generateProcessResource->load($generateProcess, $identity, GenerateProcess::IDENTITY);
            if (!$generateProcess->getId()) {
                throw new NoSuchEntityException(__('Process with specified identity "%1" not found.', $identity));
            }

            /** @var CommandResultInterface $commandResult */
            $commandResult = $this->commandResultFactory->create();
            if ($commandResultData = $generateProcess->getCommandResult()) {
                $commandResult->unserialize($commandResultData);
            }
            $generateProcess->setCommandResult($commandResult);

            $this->generateProcesses[$identity] = $generateProcess;
        }

        return $this->generateProcesses[$identity];
    }

    /**
     * @param string $identity
     * @param int|null $id
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function initiateProcess(string $identity, ?int $id = null): void
    {
        if (empty($identity)) {
            throw new LocalizedException(__('Invalid process identity'));
        }

        /** @var GenerateProcess $generateProcess */
        $generateProcess = $this->generateProcessFactory->create();
        $generateProcess
            ->setIdentity($identity)
            ->setStatus(GenerateProcess::STATUS_PENDING)
            ->setPid(null)
            ->setCommandResult(null);

        if ($id) {
            $generateProcess->setId($id);
        }
        $this->generateProcessResource->save($generateProcess);
    }

    /**
     * @param $commandResult
     * @return CommandResultInterface
     */
    private function getCommandResultObject($commandResult): CommandResultInterface
    {
        if ($commandResult instanceof CommandResultInterface) {
            $result = $commandResult;
        } elseif (is_string($commandResult)) {
            /** @var CommandResultInterface $result */
            $result = $this->commandResultFactory->create();
            $result->unserialize($commandResult);
        } else {
            $result = $this->commandResultFactory->create();
        }

        return $result;
    }

    /**
     * @param GenerateProcess $generateProcess
     * @param bool $setStatusRunning
     * @param bool $setPid
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function updateProcess(
        GenerateProcess $generateProcess,
        bool $setStatusRunning = true,
        bool $setPid = true
    ): void {
        if ($setStatusRunning) {
            $generateProcess->setStatus(GenerateProcess::STATUS_RUNNING);
        }
        if ($setPid) {
            $generateProcess->setPid(getmypid());
        }
        $generateProcessNew = clone $generateProcess;
        $generateProcessNew->setCommandResult(
            $this->getCommandResultObject($generateProcess->getCommandResult())->serialize()
        );

        $this->generateProcessResource->save($generateProcessNew);
    }

    /**
     * @param GenerateProcess $generateProcess
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function finalizeProcess(GenerateProcess $generateProcess): void
    {
        $commandResult = $this->getCommandResultObject($generateProcess->getCommandResult());
        $generateProcess
            ->setStatus($commandResult->isFailed() ? GenerateProcess::STATUS_FAILED : GenerateProcess::STATUS_SUCCESS)
            ->setFinished(true)
            ->setPid(null);
        $this->updateProcess($generateProcess, false, false);
    }

    /**
     * @param GenerateProcess $generateProcess
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function markAsFailed(GenerateProcess $generateProcess): void
    {
        $generateProcess->getCommandResult()->setFailed(true);
        $this->finalizeProcess($generateProcess);
    }
}
