<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Console\Command\Regenerate;

interface OptionResolverInterface
{
    const INPUT_KEY_STORE_ID                             = 'store-id';
    const INPUT_KEY_REGENERATE_ENTITY_TYPE               = 'entity-type';
    const INPUT_KEY_NO_REINDEX                           = 'no-reindex';
    const INPUT_KEY_NO_CACHE_FLUSH                       = 'no-cache-flush';
    const INPUT_KEY_NO_CACHE_CLEAN                       = 'no-cache-clean';
    const INPUT_KEY_IDS_RANGE                            = 'ids-range';
    const INPUT_KEY_SPECIFIC_IDS                         = 'ids';
    const INPUT_KEY_PROCESS_IDENTITY                     = 'process-identity';

    const DEFAULT_ENTITY_TYPE = 'product';

    /**
     * @return string
     */
    public function getEntity(): string;

    /**
     * @param int $storeId
     * @return array
     */
    public function getEntityIds(int $storeId): array;

    /**
     * @return bool
     */
    public function isRunReindex(): bool;

    /**
     * @return bool
     */
    public function isRunCacheFlush(): bool;

    /**
     * @return bool
     */
    public function isRunCacheClean(): bool;

    /**
     * @return array
     */
    public function getStoresToProcess(): array;

    /**
     * @return string
     */
    public function getProcessIdentity(): string;
}
