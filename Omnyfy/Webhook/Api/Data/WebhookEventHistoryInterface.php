<?php
namespace Omnyfy\Webhook\Api\Data;

interface WebhookEventHistoryInterface
{
    const WEBHOOK_ID = 'webhook_id';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const BODY = 'body';

    const STATUS = 'status';

    const EVENT_ID = 'event_id';

    /**
     * @return int
     */
    public function getId();
 
    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getWebhookId();
 
    /**
     * @param int $webhookId
     * @return $this
     */
    public function setWebhookId($webhookId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getBody();

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getEventId();

    /**
     * @param string $eventId
     * @return $this
     */
    public function setEventId($eventId);
}