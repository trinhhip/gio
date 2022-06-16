<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Api\Data;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

interface GenerateConfigInterface extends ExtensibleDataInterface
{
    /**
     * @return bool
     */
    public function isIncludeToRegeneration(): bool;

    /**
     * @param bool $includeToRegeneration
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setIncludeToRegeneration(bool $includeToRegeneration): GenerateConfigInterface;

    /**
     * @return string|null
     */
    public function getStoreId(): ?string;

    /**
     * @param string|null $storeId
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setStoreId(?string $storeId): GenerateConfigInterface;

    /**
     * @return string|null
     */
    public function getRegenerateEntityType(): ?string;

    /**
     * @param string|null $regenerateEntityType
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setRegenerateEntityType(?string $regenerateEntityType): GenerateConfigInterface;

    /**
     * @return string|null
     */
    public function getIdsRange(): ?string;

    /**
     * @param string|null $idsRange
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setIdsRange(?string $idsRange): GenerateConfigInterface;

    /**
     * @return string|null
     */
    public function getSpecificIds(): ?string;

    /**
     * @param int|null $specificIds
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setSpecificIds(?string $specificIds): GenerateConfigInterface;

    /**
     * @return bool
     */
    public function isNoReindex(): bool;

    /**
     * @param bool $noReindex
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setNoReindex(bool $noReindex): GenerateConfigInterface;

    /**
     * @return bool
     */
    public function isNoCacheFlush(): bool;

    /**
     * @param bool $noCacheFlush
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setNoCacheFlush(bool $noCacheFlush): GenerateConfigInterface;

    /**
     * @return bool
     */
    public function isNoCacheClean(): bool;

    /**
     * @param bool $noCacheClean
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setNoCacheClean(bool $noCacheClean): GenerateConfigInterface;

    /**
     * @return string
     */
    public function getProcessIdentity(): string;

    /**
     * @param string $processIdentity
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setProcessIdentity(string $processIdentity): GenerateConfigInterface;

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigExtensionInterface|null
     */
    public function getExtensionAttributes(): ?GenerateConfigExtensionInterface;

    /**
     * @param \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigExtensionInterface $extensionAttributes
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateConfigInterface
     */
    public function setExtensionAttributes(
        GenerateConfigExtensionInterface $extensionAttributes
    ): GenerateConfigInterface;
}
