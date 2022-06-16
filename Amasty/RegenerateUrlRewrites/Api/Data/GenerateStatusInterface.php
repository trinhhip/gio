<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Api\Data;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

interface GenerateStatusInterface extends ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface
     */
    public function setStatus(string $status): GenerateStatusInterface;

    /**
     * @return int
     */
    public function getProceed(): int;

    /**
     * @param int $proceed
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface
     */
    public function setProceed(int $proceed): GenerateStatusInterface;

    /**
     * @return ?int
     */
    public function getPid(): ?int;

    /**
     * @param ?int $pid
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface
     */
    public function setPid(?int $pid): GenerateStatusInterface;

    /**
     * @return int
     */
    public function getTotal(): int;

    /**
     * @param int $total
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface
     */
    public function setTotal(int $total): GenerateStatusInterface;

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusMessageInterface[]
     */
    public function getMessages(): array;

    /**
     * @param array $messages
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface
     */
    public function setMessages(array $messages): GenerateStatusInterface;

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusMessageInterface[]
     */
    public function getError(): ?array;

    /**
     * @param array $errorMessage
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface
     */
    public function setError(array $errorMessage): GenerateStatusInterface;

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusExtensionInterface|null
     */
    public function getExtensionAttributes(): ?GenerateStatusExtensionInterface;

    /**
     * @param \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusExtensionInterface $extensionAttributes
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface
     */
    public function setExtensionAttributes(
        GenerateStatusExtensionInterface $extensionAttributes
    ): GenerateStatusInterface;
}
