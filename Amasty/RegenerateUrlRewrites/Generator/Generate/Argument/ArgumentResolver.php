<?php

declare(strict_types = 1);

namespace Amasty\RegenerateUrlRewrites\Generator\Generate\Argument;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface;
use Amasty\RegenerateUrlRewrites\Console\Command\Regenerate\OptionResolverInterface;

class ArgumentResolver
{
    /**
     * @param GenerateConfigInterface $config
     * @return string
     */
    public function getArguments(GenerateConfigInterface $config): string
    {
        $arguments = [
            OptionResolverInterface::INPUT_KEY_STORE_ID => $config->getStoreId(),
            OptionResolverInterface::INPUT_KEY_REGENERATE_ENTITY_TYPE => $config->getRegenerateEntityType(),
            OptionResolverInterface::INPUT_KEY_IDS_RANGE => $config->getIdsRange(),
            OptionResolverInterface::INPUT_KEY_SPECIFIC_IDS => $config->getSpecificIds(),
            OptionResolverInterface::INPUT_KEY_PROCESS_IDENTITY => $config->getProcessIdentity(),
            OptionResolverInterface::INPUT_KEY_NO_REINDEX => $config->isNoReindex(),
            OptionResolverInterface::INPUT_KEY_NO_CACHE_FLUSH => $config->isNoCacheFlush(),
            OptionResolverInterface::INPUT_KEY_NO_CACHE_CLEAN => $config->isNoCacheClean()
        ];

        $argumentsOutput = [];
        foreach ($arguments as $argumentName => $argumentValue) {
            if ((is_bool($argumentValue) && !$argumentValue)
                || ($argumentValue === null)
            ) {
                continue;
            }

            $argumentsOutput[] = sprintf(
                '--%s%s%s',
                $argumentName,
                is_bool($argumentValue) ? '' : '=',
                !is_bool($argumentValue) ? $argumentValue : ''
            );
        }

        return implode(' ', $argumentsOutput);
    }
}
