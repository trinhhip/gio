<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Generator\Generate;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultExtensionInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterface;
use Magento\Framework\DataObject;

class StartResult extends DataObject implements GenerateStartResultInterface
{
    const PROCESS_IDENTITY = 'process_identity';
    const ERROR = 'error';

    /**
     * @return string|null
     */
    public function getProcessIdentity(): ?string
    {
        $processIdentity = $this->getData(self::PROCESS_IDENTITY);
        return $processIdentity === null ? null : (string)$processIdentity;
    }

    /**
     * @param string|null $processIdentity
     * @return GenerateStartResultInterface
     */
    public function setProcessIdentity(?string $processIdentity): GenerateStartResultInterface
    {
        $this->setData(self::PROCESS_IDENTITY, $processIdentity);

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
     * @return GenerateStartResultInterface
     */
    public function setError(array $errorMessage): GenerateStartResultInterface
    {
        $this->setData(self::ERROR, $errorMessage);

        return $this;
    }

    /**
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?GenerateStartResultExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @param \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultExtensionInterface $extensionAttributes
     * @return GenerateStartResultInterface
     */
    public function setExtensionAttributes(
        GenerateStartResultExtensionInterface $extensionAttributes
    ): GenerateStartResultInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
