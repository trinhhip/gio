<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Generator\Command;

use Amasty\RegenerateUrlRewrites\Generator\Generate\Status\Message;
use Magento\Framework\DataObject;

class CommandResult implements CommandResultInterface
{
    const IS_FAILED = 'is_failed';
    const MESSAGES = 'messages';
    const TOTAL_RECORDS = 'total_records';
    const RECORDS_PROCESSED = 'records_processed';

    /** @var array */
    private $defaultValues = [
        self::IS_FAILED => false,
        self::MESSAGES => [],
        self::TOTAL_RECORDS => 0,
        self::RECORDS_PROCESSED => 0
    ];

    /**
     * @var DataObject
     */
    private $result;

    public function __construct()
    {
        $this->result = new DataObject($this->defaultValues);
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return (bool)$this->result->getData(self::IS_FAILED);
    }

    /**
     * @param bool $failed
     * @return void
     */
    public function setFailed(bool $failed = false): void
    {
        $this->result->setData(self::IS_FAILED, $this->result->getData(self::IS_FAILED) || $failed);
    }

    /**
     * @param int $type
     * @param $message
     * @return void
     */
    public function logMessage(int $type, $message): void
    {
        $messages = $this->getMessages();
        array_unshift($messages, [Message::TYPE => $type, Message::MESSAGE => (string)$message]);
        $this->setMessages($messages);
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return (array)$this->result->getData(self::MESSAGES);
    }

    /**
     * @param array $messages
     * @return void
     */
    public function setMessages(array $messages): void
    {
        $this->result->setData(self::MESSAGES, $messages);
    }

    /**
     * @return void
     */
    public function clearMessages(): void
    {
        $this->result->setData(self::MESSAGES, []);
    }

    /**
     * @return int
     */
    public function getTotalRecords(): int
    {
        return (int)$this->result->getData(self::TOTAL_RECORDS);
    }

    /**
     * @param int $records
     * @return void
     */
    public function setTotalRecords(int $records): void
    {
        $this->result->setData(self::TOTAL_RECORDS, $records);
    }

    /**
     * @return int
     */
    public function getRecordsProcessed(): int
    {
        return (int)$this->result->getData(self::RECORDS_PROCESSED);
    }

    /**
     * @param int $records
     * @return void
     */
    public function setRecordsProcessed(int $records): void
    {
        $this->result->setData(self::RECORDS_PROCESSED, $records);
    }

    /**
     * @return false|string|null
     */
    public function serialize()
    {
        return json_encode($this->result->getData());
    }

    /**
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized): void
    {
        $this->result = new DataObject(json_decode($serialized, true));
    }
}
