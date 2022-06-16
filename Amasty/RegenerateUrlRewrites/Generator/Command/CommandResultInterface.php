<?php

namespace Amasty\RegenerateUrlRewrites\Generator\Command;

interface CommandResultInterface extends \Serializable
{
    const MESSAGE_CRITICAL = 50;
    const MESSAGE_ERROR = 40;
    const MESSAGE_WARNING = 30;
    const MESSAGE_INFO = 20;
    const MESSAGE_DEBUG = 10;

    /**
     * @return bool
     */
    public function isFailed(): bool;

    /**
     * @param bool $failed
     * @return void
     */
    public function setFailed(bool $failed = false): void;

    /**
     * @param int $type
     * @param $message
     * @return void
     */
    public function logMessage(int $type, $message): void;

    /**
     * @return array
     */
    public function getMessages(): array;

    /**
     * @param array $messages
     * @return void
     */
    public function setMessages(array $messages): void;

    /**
     * @return void
     */
    public function clearMessages(): void;

    /**
     * @return int
     */
    public function getTotalRecords(): int;

    /**
     * @param int $records
     * @return void
     */
    public function setTotalRecords(int $records): void;

    /**
     * @return int
     */
    public function getRecordsProcessed(): int;

    /**
     * @param int $records
     * @return void
     */
    public function setRecordsProcessed(int $records): void;
}
