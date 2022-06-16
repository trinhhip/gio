<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Console\Command\Regenerate;

use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\GenerateProcess;
use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\GenerateProcessRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Helper\ProgressBarFactory;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressManager
{
    const MESSAGE_TYPE_DEFAULT = 'default';
    const MESSAGE_TYPE_INFO = 'info';
    const MESSAGE_TYPE_ERROR = 'error';

    /**
     * @var GenerateProcess|null
     */
    private $generateProcess = null;

    /**
     * @var \Symfony\Component\Console\Helper\ProgressBar|null
     */
    private $progressBar = null;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var GenerateProcessRepository
     */
    private $generateProcessRepository;

    /**
     * @var ProgressBarFactory
     */
    private $progressBarFactory;

    public function __construct(
        GenerateProcessRepository $generateProcessRepository,
        ProgressBarFactory $progressBarFactory
    ) {
        $this->generateProcessRepository = $generateProcessRepository;
        $this->progressBarFactory = $progressBarFactory;
    }

    /**
     * @param OptionResolverInterface $options
     * @param OutputInterface $output
     * @return void
     */
    public function initialize(OptionResolverInterface $options, OutputInterface $output): void
    {
        $this->output = $output;

        try {
            if ($processIdentity = $options->getProcessIdentity()) {
                $this->generateProcess = $this->generateProcessRepository->getByIdentity($processIdentity);
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
            // Do nothing
        }
    }

    /**
     * @return void
     */
    public function finalizeProcess(): void
    {
        if ($this->generateProcess) {
            $this->generateProcessRepository->finalizeProcess($this->generateProcess);
        }
    }

    /**
     * @param string|null $errorMessage
     * @return void
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        if ($errorMessage) {
            $this->addErrorMessage($errorMessage);
        }
        if ($this->generateProcess) {
            $this->generateProcessRepository->markAsFailed($this->generateProcess);
        }
    }

    /**
     * @param string $type
     * @return string[]
     */
    private function getMessageTags(string $type): array
    {
        $tags = ['', ''];
        switch ($type) {
            case self::MESSAGE_TYPE_INFO:
                $tags = ['<info>', '</info>'];
                break;
            case self::MESSAGE_TYPE_ERROR:
                $tags = ['<error>', '</error>'];
                break;
        }

        return $tags;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getMessageMethod(string $type): string
    {
        $method = 'addInfoMessage';
        switch ($type) {
            case self::MESSAGE_TYPE_ERROR:
                $method = 'addErrorMessage';
                break;
        }

        return $method;
    }

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    public function addMessage(string $message, string $type = self::MESSAGE_TYPE_DEFAULT): void
    {
        list($otag, $ctag) = $this->getMessageTags($type);
        $this->output->writeln($otag . $message . $ctag);

        if ($this->generateProcess) {
            $method = $this->getMessageMethod($type);
            $this->generateProcess->$method($message);
            $this->generateProcessRepository->updateProcess($this->generateProcess);
        }
    }

    /**
     * @param string $message
     * @return void
     */
    public function addInfoMessage(string $message): void
    {
        $this->addMessage($message, self::MESSAGE_TYPE_INFO);
    }

    /**
     * @param string $message
     * @return void
     */
    public function addErrorMessage(string $message): void
    {
        $this->addMessage($message, self::MESSAGE_TYPE_ERROR);
    }

    /**
     * @param int $max
     * @return void
     */
    public function initializeProgressBar(int $max): void
    {
        $this->progressBar = $this->progressBarFactory->create(
            [
                'output' => $this->output,
                'max' => $max
            ]
        );
        $this->progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%% %elapsed% %memory:6s% \t| <info>Current ID: %message%</info>"
        );
    }

    /**
     * @param string $message
     * @return void
     */
    public function advanceProgressBar(string $message): void
    {
        if ($this->progressBar) {
            $this->progressBar->setMessage($message);
            $this->progressBar->advance();
        }
    }
}
