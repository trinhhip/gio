<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Generator\Generate\Status;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusMessageInterface;
use Magento\Framework\DataObject;

class Message extends DataObject implements GenerateStatusMessageInterface
{
    const TYPE = 'type';
    const MESSAGE = 'message';

    /**
     * @return int
     */
    public function getType(): int
    {
        return (int)$this->getData(self::TYPE);
    }

    /**
     * @param int $type
     * @return GenerateStatusMessageInterface
     */
    public function setType(int $type): GenerateStatusMessageInterface
    {
        $this->setData(self::TYPE, $type);

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return (string)$this->getData(self::MESSAGE);
    }

    /**
     * @param string $message
     * @return GenerateStatusMessageInterface
     */
    public function setMessage(string $message): GenerateStatusMessageInterface
    {
        $this->setData(self::MESSAGE, $message);

        return $this;
    }
}
