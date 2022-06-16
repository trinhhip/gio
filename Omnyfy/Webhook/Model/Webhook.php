<?php
namespace Omnyfy\Webhook\Model;
use Omnyfy\Webhook\Api\Data\WebhookInterface;

class Webhook extends \Magento\Framework\Model\AbstractModel implements WebhookInterface
{
    const STATUS_DISABLED = 0;

    const STATUS_ENABLED = 1;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Webhook\Model\ResourceModel\Webhook');
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return int
     */
    public function getWebhookTypeId()
    {
        return $this->getData(self::WEBHOOK_TYPE_ID);
    }

    /**
     * @param int $webhookTypeId
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     */
    public function setWebhookTypeId($webhookTypeId)
    {
        return $this->setData(self::WEBHOOK_TYPE_ID, $webhookTypeId);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->getData(self::METHOD);
    }

    /**
     * @param string $method
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     */
    public function setMethod($method)
    {
        return $this->setData(self::METHOD, $method);
    }

    /**
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->getData(self::ENDPOINT_URL);
    }

    /**
     * @param string $endpointUrl
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     */
    public function setEndpointUrl($endpointUrl)
    {
        return $this->setData(self::ENDPOINT_URL, $endpointUrl);
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->getData(self::CONTENT_TYPE);
    }

    /**
     * @param string $contentType
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     */
    public function setContentType($contentType)
    {
        return $this->setData(self::CONTENT_TYPE, $contentType);
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
