<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Console\Command\Regenerate;

use Amasty\RegenerateUrlRewrites\Generator\Generate\Config\ConfigResolver;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class OptionResolver extends DataObject implements OptionResolverInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @var InputValidator
     */
    private $inputValidator;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigResolver $configResolver,
        InputValidator $inputValidator,
        array $data = []
    ) {
        parent::__construct($data);
        $this->storeManager = $storeManager;
        $this->configResolver = $configResolver;
        $this->inputValidator = $inputValidator;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->getData(OptionResolverInterface::INPUT_KEY_REGENERATE_ENTITY_TYPE)
            ?: OptionResolverInterface::DEFAULT_ENTITY_TYPE;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getEntityIds(int $storeId): array
    {
        if (!$this->isDefaultScope($storeId) && $this->isProcessStartedFromBackend()) {
            $entityType = $this->getEntity();
            $config = $this->configResolver->fromSettings($entityType, $storeId);
            if (!$config->isIncludeToRegeneration()) {
                throw new LocalizedException(__('Entity %1 is not included in regeneration', $entityType));
            }

            $specificIds = $config->getSpecificIds();
            $this->inputValidator->validateSpecificIdsOption($specificIds);
            $this->setData(
                OptionResolverInterface::INPUT_KEY_SPECIFIC_IDS,
                $specificIds
            );

            $idsRange = $config->getIdsRange();
            $this->inputValidator->validateIdsRangeOption($idsRange);
            $this->setData(
                OptionResolverInterface::INPUT_KEY_IDS_RANGE,
                $idsRange
            );
        }

        return $this->getEntityIdsFromOptions();
    }

    /**
     * @return array
     */
    private function getEntityIdsFromOptions(): array
    {
        $entityIds = [];
        if ($idsRange = $this->getData(OptionResolverInterface::INPUT_KEY_IDS_RANGE)) {
            $entityIds = $this->generateIdsRange($idsRange);
        }
        if ($specificIds = $this->getData(OptionResolverInterface::INPUT_KEY_SPECIFIC_IDS)) {
            $entityIds = array_merge($entityIds, $this->generateSpecificIds($specificIds));
        }
        if ($idsRange && $specificIds) {
            $entityIds = array_unique($entityIds);
        }
        if ($specificIds) {
            sort($entityIds, SORT_NUMERIC);
        }

        return $entityIds;
    }

    /**
     * @return bool
     */
    public function isRunReindex(): bool
    {
        return !$this->getData(OptionResolverInterface::INPUT_KEY_NO_REINDEX);
    }

    /**
     * @return bool
     */
    public function isRunCacheFlush(): bool
    {
        return !$this->getData(OptionResolverInterface::INPUT_KEY_NO_CACHE_FLUSH);
    }

    /**
     * @return bool
     */
    public function isRunCacheClean(): bool
    {
        return !$this->getData(OptionResolverInterface::INPUT_KEY_NO_CACHE_CLEAN);
    }

    /**
     * @return array
     */
    public function getStoresToProcess(): array
    {
        $storeId = $this->getData(OptionResolverInterface::INPUT_KEY_STORE_ID);
        if ($storeId !== null) {
            return [(int)$storeId];
        }

        return $this->getAllStoreIds();
    }

    /**
     * @param string $idsRange
     * @return array
     */
    private function generateIdsRange(string $idsRange): array
    {
        list($first, $last) = array_map('intval', explode('-', $idsRange, 2));
        if ($last < $first) {
            $tmp = $last;
            $last = $first;
            $first = $tmp;
        }

        return range($first, $last);
    }

    /**
     * @param string $specificIds
     * @return array
     */
    private function generateSpecificIds(string $specificIds): array
    {
        return array_map('intval', explode(',', $specificIds));
    }

    /**
     * @return string
     */
    public function getProcessIdentity(): string
    {
        return (string)$this->getData(OptionResolverInterface::INPUT_KEY_PROCESS_IDENTITY);
    }

    /**
     * Get list of all stores ids
     *
     * @return array
     */
    private function getAllStoreIds(): array
    {
        $stores = [];
        foreach ($this->storeManager->getStores(true) as $store) {
            $stores[] = (int)$store->getId();
        }
        sort($stores, SORT_NUMERIC);

        return $stores;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    private function isDefaultScope(int $storeId): bool
    {
        return $storeId == 0;
    }

    /**
     * @return bool
     */
    private function isProcessStartedFromBackend(): bool
    {
        return (bool)$this->getProcessIdentity();
    }
}
