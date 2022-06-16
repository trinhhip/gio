<?php
namespace Omnyfy\Webhook\Model;
use Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface;

class WebhookEventHistory extends \Magento\Framework\Model\AbstractModel implements WebhookEventHistoryInterface
{
    const STATUS_SUCCESS = 1;

    const STATUS_FAIL = 0;
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Webhook\Model\ResourceModel\WebhookEventHistory');
    }

    /**
     * @return int
     */
    public function getWebhookId()
    {
        return $this->getData(self::WEBHOOK_ID);
    }

    /**
     * @param int $webhookId
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface
     */
    public function setWebhookId($webhookId)
    {
        return $this->setData(self::WEBHOOK_ID, $webhookId);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->getData(self::BODY);
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        return $this->setData(self::BODY, $body);
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this->getData(self::EVENT_ID);
    }

    /**
     * @param string $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        return $this->setData(self::EVENT_ID, $eventId);

    }
}
