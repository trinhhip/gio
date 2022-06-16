<?php
namespace Omnyfy\Webhook\Model;
use Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface;

class WebhookEventSchedule extends \Magento\Framework\Model\AbstractModel implements WebhookEventScheduleInterface
{
    const STATUS_PENDING = 1;

    const STATUS_INPROGRESS = 2;

    const STATUS_SUCCESS = 3;

    const STATUS_FAILED = 4;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Webhook\Model\ResourceModel\WebhookEventSchedule');
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface
     */
    public function setWebhookId($webhookId)
    {
        return $this->setData(self::WEBHOOK_ID, $webhookId);
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface
     */
    public function setBody($body)
    {
        return $this->setData(self::BODY, $body);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}