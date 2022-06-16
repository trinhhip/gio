<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Api\Data;

interface GenerateStatusMessageInterface
{
    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @param int $type
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusMessageInterface
     */
    public function setType(int $type): GenerateStatusMessageInterface;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $message
     * @return \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusMessageInterface
     */
    public function setMessage(string $message): GenerateStatusMessageInterface;
}
