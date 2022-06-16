<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Api\Data;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

interface GenerateStartResultInterface extends ExtensibleDataInterface
{
    /**
     * @return ?string
     */
    public function getProcessIdentity(): ?string;

    /**
     * @param string $processIdentity
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterface
     */
    public function setProcessIdentity(?string $processIdentity): GenerateStartResultInterface;

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusMessageInterface[]
     */
    public function getError(): ?array;

    /**
     * @param array $errorMessage
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterface
     */
    public function setError(array $errorMessage): GenerateStartResultInterface;

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?GenerateStartResultExtensionInterface;

    /**
     * @param \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultExtensionInterface $extensionAttributes
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterface
     */
    public function setExtensionAttributes(
        GenerateStartResultExtensionInterface $extensionAttributes
    ): GenerateStartResultInterface;
}
