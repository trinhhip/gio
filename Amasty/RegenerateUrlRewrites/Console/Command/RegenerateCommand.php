<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Console\Command;

use Amasty\RegenerateUrlRewrites\Console\Command\Regenerate\InputValidator;
use Amasty\RegenerateUrlRewrites\Console\Command\Regenerate\OptionResolverInterface;
use Amasty\RegenerateUrlRewrites\Console\Command\Regenerate\OptionResolverInterfaceFactory;
use Amasty\RegenerateUrlRewrites\Console\Command\Regenerate\ProgressManager;
use Amasty\RegenerateUrlRewrites\Model\ConfigProvider;
use Amasty\RegenerateUrlRewrites\Model\ProcessorPool;
use InvalidArgumentException;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateCommand extends Command
{
    /**
     * @var AppState $appState
     */
    private $appState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProcessorPool
     */
    private $processorPool;

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * @var OptionResolverInterfaceFactory
     */
    private $optionResolverFactory;

    /**
     * @var ProgressManager
     */
    private $progressManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        AppState $appState,
        StoreManagerInterface $storeManager,
        ProcessorPool $processorPool,
        InputValidator $inputValidator,
        OptionResolverInterfaceFactory $optionResolverFactory,
        ProgressManager $progressManager,
        ConfigProvider $configProvider
    ) {
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->processorPool = $processorPool;
        $this->inputValidator = $inputValidator;
        $this->optionResolverFactory = $optionResolverFactory;
        $this->progressManager = $progressManager;
        $this->configProvider = $configProvider;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('amurlrewrites:regenerate')
            ->setDescription('Regenerate Url rewrites')
            ->setDefinition([
                new InputOption(
                    OptionResolverInterface::INPUT_KEY_STORE_ID,
                    null,
                    InputArgument::OPTIONAL,
                    'Specific store id'
                ),
                new InputOption(
                    OptionResolverInterface::INPUT_KEY_REGENERATE_ENTITY_TYPE,
                    null,
                    InputArgument::OPTIONAL,
                    'Entity type which URLs regenerate: default is "product".'
                ),
                new InputOption(
                    OptionResolverInterface::INPUT_KEY_NO_REINDEX,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not run reindex when URL rewrites are generated.'
                ),
                new InputOption(
                    OptionResolverInterface::INPUT_KEY_NO_CACHE_FLUSH,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not run cache:flush when URL rewrites are generated.'
                ),
                new InputOption(
                    OptionResolverInterface::INPUT_KEY_NO_CACHE_CLEAN,
                    null,
                    InputOption::VALUE_NONE,
                    'Do not run cache:clean when URL rewrites are generated.'
                ),
                new InputOption(
                    OptionResolverInterface::INPUT_KEY_IDS_RANGE,
                    null,
                    InputArgument::OPTIONAL,
                    'IDs range, e.g.: 15-40'
                ),
                new InputOption(
                    OptionResolverInterface::INPUT_KEY_SPECIFIC_IDS,
                    null,
                    InputArgument::OPTIONAL,
                    'Specific IDs, e.g.: 1,2,3'
                ),
                new InputOption(
                    OptionResolverInterface::INPUT_KEY_PROCESS_IDENTITY,
                    null,
                    InputArgument::OPTIONAL,
                    'Process identity. Used when running a command from the backend.'
                ),
            ]);
    }

    /**
     * Regenerate Url Rewrites
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var OptionResolverInterface $options */
        $options = $this->optionResolverFactory->create(['data' => $input->getOptions()]);
        $this->progressManager->initialize($options, $output);

        if (!$this->configProvider->isEnabled()) {
            $this->progressManager->addInfoMessage('Module is disabled');
            $this->progressManager->markAsFailed();

            return;
        }

        try {
            $this->inputValidator->validate($input);
        } catch (InvalidArgumentException $e) {
            $this->progressManager->markAsFailed($e->getMessage());

            return;
        }

        $this->progressManager->addMessage('Regenerating URL Rewrites:');

        try {
            $this->appState->getAreaCode();
        } catch (LocalizedException $e) {
            $this->appState->setAreaCode('adminhtml');
        }

        foreach ($options->getStoresToProcess() as $storeId) {
            $this->storeManager->setCurrentStore($storeId);
            $this->progressManager->addInfoMessage(
                sprintf(
                    'Generating %s url rewrites for Store ID: %s.',
                    $options->getEntity(),
                    $storeId
                )
            );

            try {
                $entityIds = $options->getEntityIds($storeId);
            } catch (\Exception $e) {
                $this->progressManager->addErrorMessage($e->getMessage());
                continue;
            }

            $generator = $this->processorPool->getProcessor($options->getEntity())->process(
                $storeId,
                $entityIds
            );

            $this->progressManager->initializeProgressBar($generator->current());

            while ($generator->valid()) {
                $regenerateInfo = $generator->key();
                if ($regenerateInfo['error'] !== '') {
                    $this->progressManager->addErrorMessage((string)$regenerateInfo['error']);
                }

                $this->progressManager->advanceProgressBar($regenerateInfo['entityId']);
                $generator->next();
            }

            $output->writeln(PHP_EOL);
        }

        $this->runSubCommands($options, $output);
        $this->progressManager->addMessage('Finished');
        $this->progressManager->finalizeProcess();
    }

    /**
     * Run Sub Commands
     *
     * @param OptionResolverInterface $options
     * @param OutputInterface $output
     * @return void
     */
    private function runSubCommands(
        OptionResolverInterface $options,
        OutputInterface $output
    ): void {
        if ($options->isRunReindex()) {
            $reindexCommand = 'indexer:reindex';
            $commandInput = new ArrayInput(['command' => $reindexCommand]);
            $this->progressManager->addInfoMessage(sprintf('Executing the command "%s"', $reindexCommand));
            $this->getApplication()->find($reindexCommand)->run($commandInput, $output);
            $this->progressManager->addMessage('Done');
        }

        if ($options->isRunCacheClean()) {
            $cacheCleanCommand = 'cache:clean';
            $this->progressManager->addInfoMessage(sprintf('Executing the command "%s"', $cacheCleanCommand));
            $commandInput = new ArrayInput(['command' => $cacheCleanCommand]);
            $this->getApplication()->find($cacheCleanCommand)->run($commandInput, $output);
        }

        if ($options->isRunCacheFlush()) {
            $cacheFlushCommand = 'cache:flush';
            $this->progressManager->addInfoMessage(sprintf('Executing the command "%s"', $cacheFlushCommand));
            $commandInput = new ArrayInput(['command' => $cacheFlushCommand]);
            $this->getApplication()->find($cacheFlushCommand)->run($commandInput, $output);
        }
    }
}
