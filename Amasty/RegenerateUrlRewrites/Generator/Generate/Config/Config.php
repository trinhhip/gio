<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Generator\Generate\Config;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigExtensionInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface;
use Magento\Framework\DataObject;

class Config extends DataObject implements GenerateConfigInterface
{
    const INCLUDE_TO_REGENERATION = 'include_to_regeneration';
    const STORE_ID = 'store_id';
    const REGENERATE_ENTITY_TYPE = 'regenerate_entity_type';
    const IDS_RANGE = 'ids_range';
    const SPECIFIC_IDS = 'specific_ids';
    const NO_REINDEX = 'no_reindex';
    const NO_CACHE_FLUSH = 'no_cache_flush';
    const NO_CACHE_CLEAN = 'no_cache_clean';
    const PROCESS_IDENTITY = 'process_identity';

    /**
     * @return bool
     */
    public function isIncludeToRegeneration(): bool
    {
        return (bool)$this->getData(self::INCLUDE_TO_REGENERATION);
    }

    /**
     * @param bool $includeToRegeneration
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setIncludeToRegeneration(bool $includeToRegeneration): GenerateConfigInterface
    {
        $this->setData(self::INCLUDE_TO_REGENERATION, $includeToRegeneration);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStoreId(): ?string
    {
        $storeId = $this->getData(self::STORE_ID);
        return $storeId === null ? null : (string)$storeId;
    }

    /**
     * @param string|null $storeId
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setStoreId(?string $storeId): GenerateConfigInterface
    {
        $this->setData(self::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegenerateEntityType(): ?string
    {
        $regenerateEntityType = $this->getData(self::REGENERATE_ENTITY_TYPE);
        return $regenerateEntityType === null ? null : (string)$regenerateEntityType;
    }

    /**
     * @param string|null $regenerateEntityType
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setRegenerateEntityType(?string $regenerateEntityType): GenerateConfigInterface
    {
        $this->setData(self::REGENERATE_ENTITY_TYPE, $regenerateEntityType);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdsRange(): ?string
    {
        $idsRange = $this->getData(self::IDS_RANGE);
        return $idsRange === null ? null : (string)$idsRange;
    }

    /**
     * @param string|null $idsRange
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setIdsRange(?string $idsRange): GenerateConfigInterface
    {
        $this->setData(self::IDS_RANGE, $idsRange);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSpecificIds(): ?string
    {
        $specificId = $this->getData(self::SPECIFIC_IDS);
        return $specificId === null ? null : (string)$specificId;
    }

    /**
     * @param string|null $specificIds
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setSpecificIds(?string $specificIds): GenerateConfigInterface
    {
        $this->setData(self::SPECIFIC_IDS, $specificIds);

        return $this;
    }

    /**
     * @return bool
     */
    public function isNoReindex(): bool
    {
        return (bool)$this->getData(self::NO_REINDEX);
    }

    /**
     * @param bool $noReindex
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setNoReindex(bool $noReindex): GenerateConfigInterface
    {
        $this->setData(self::NO_REINDEX, $noReindex);

        return $this;
    }

    /**
     * @return bool
     */
    public function isNoCacheFlush(): bool
    {
        return (bool)$this->getData(self::NO_CACHE_FLUSH);
    }

    /**
     * @param bool $noCacheFlush
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setNoCacheFlush(bool $noCacheFlush): GenerateConfigInterface
    {
        $this->setData(self::NO_CACHE_FLUSH, $noCacheFlush);

        return $this;
    }

    /**
     * @return bool
     */
    public function isNoCacheClean(): bool
    {
        return (bool)$this->getData(self::NO_CACHE_CLEAN);
    }

    /**
     * @param bool $noCacheClean
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setNoCacheClean(bool $noCacheClean): GenerateConfigInterface
    {
        $this->setData(self::NO_CACHE_CLEAN, $noCacheClean);

        return $this;
    }

    /**
     * @return string
     */
    public function getProcessIdentity(): string
    {
        return (string)$this->getData(self::PROCESS_IDENTITY);
    }

    /**
     * @param string $processIdentity
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setProcessIdentity(string $processIdentity): GenerateConfigInterface
    {
        $this->setData(self::PROCESS_IDENTITY, $processIdentity);

        return $this;
    }

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigExtensionInterface|null
     */
    public function getExtensionAttributes(): ?GenerateConfigExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @param \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigExtensionInterface $extensionAttributes
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setExtensionAttributes(
        GenerateConfigExtensionInterface $extensionAttributes
    ): GenerateConfigInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
