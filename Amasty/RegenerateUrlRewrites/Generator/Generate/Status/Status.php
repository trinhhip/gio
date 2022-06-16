<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Generator\Generate\Status;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusExtensionInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface;
use Magento\Framework\DataObject;

class Status extends DataObject implements GenerateStatusInterface
{
    const STATUS = 'status';
    const PROCEED = 'proceed';
    const PID = 'pid';
    const TOTAL = 'total';
    const MESSAGES = 'messages';
    const ERROR = 'error';

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return (string)$this->getData(self::STATUS);
    }

    /**
     * @param string $status
     * @return GenerateStatusInterface
     */
    public function setStatus(string $status): GenerateStatusInterface
    {
        $this->setData(self::STATUS, $status);

        return $this;
    }

    /**
     * @return int
     */
    public function getProceed(): int
    {
        return (int)$this->getData(self::PROCEED);
    }

    /**
     * @param int $proceed
     * @return GenerateStatusInterface
     */
    public function setProceed(int $proceed): GenerateStatusInterface
    {
        $this->setData(self::PROCEED, $proceed);

        return $this;
    }

    /**
     * @return ?int
     */
    public function getPid(): ?int
    {
        $pid = $this->getData(self::PID);
        return $pid === null ? null : (int)$pid;
    }

    /**
     * @param ?int $pid
     * @return GenerateStatusInterface
     */
    public function setPid(?int $pid): GenerateStatusInterface
    {
        $this->setData(self::PID, $pid);

        return $this;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return (int)$this->getData(self::TOTAL);
    }

    /**
     * @param int $total
     * @return GenerateStatusInterface
     */
    public function setTotal(int $total): GenerateStatusInterface
    {
        $this->setData(self::TOTAL, $total);

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return (array)$this->getData(self::MESSAGES);
    }

    /**
     * @param array $messages
     * @return GenerateStatusInterface
     */
    public function setMessages(array $messages): GenerateStatusInterface
    {
        $this->setData(self::MESSAGES, $messages);

        return $this;
    }

    /**
     * @return array|null
     */
    public function getError(): ?array
    {
        $errorMessage = $this->getData(self::ERROR);
        return $errorMessage === null ? null : (array)$errorMessage;
    }

    /**
     * @param array $errorMessage
     * @return GenerateStatusInterface
     */
    public function setError(array $errorMessage): GenerateStatusInterface
    {
        $this->setData(self::ERROR, $errorMessage);

        return $this;
    }

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusExtensionInterface|null
     */
    public function getExtensionAttributes(): ?GenerateStatusExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @param \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusExtensionInterface $extensionAttributes
     * @return GenerateStatusInterface
     */
    public function setExtensionAttributes(
        GenerateStatusExtensionInterface $extensionAttributes
    ): GenerateStatusInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
