<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Model;

use Amasty\RegenerateUrlRewrites\Model\Processor\ProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class ProcessorPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $processors;

    /**
     * @var ProcessorInterface[]
     */
    private $initializedProcessors = [];

    public function __construct(ObjectManagerInterface $objectManager, array $processors = [])
    {
        $this->processors = $processors;
        $this->objectManager = $objectManager;
    }

    /**
     * @return ProcessorInterface[]
     * @throws LocalizedException
     */
    public function getProcessors(): array
    {
        foreach ($this->processors as $processorCode) {
            if (!isset($this->initializedProcessors[$processorCode])) {
                $this->initProcessor($processorCode);
            }
        }

        return $this->initializedProcessors;
    }

    /**
     * @param string $entityCode
     * @return ProcessorInterface
     * @throws LocalizedException
     */
    public function getProcessor(string $entityCode): ProcessorInterface
    {
        if (!isset($this->initializedProcessors[$entityCode])) {
            $this->initProcessor($entityCode);
        }

        return $this->initializedProcessors[$entityCode];
    }

    /**
     * @return array
     */
    public function getEntityCodes(): array
    {
        return array_keys($this->processors);
    }

    /**
     * @param string $entityCode
     * @return void
     * @throws LocalizedException
     */
    private function initProcessor(string $entityCode): void
    {
        if (!empty($this->processors[$entityCode])) {
            $processor = $this->objectManager->create($this->processors[$entityCode]);
            if (!$processor instanceof ProcessorInterface) {
                throw new LocalizedException(
                    __('Processor %1 must implement %2 interface', $entityCode, ProcessorInterface::class)
                );
            }

            $this->initializedProcessors[$entityCode] = $processor;
        } else {
            throw new LocalizedException(__('Processor with %1 code is not declared', $entityCode));
        }
    }
}
