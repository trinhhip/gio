<?php
namespace Omnyfy\Webhook\Model;
use Omnyfy\Webhook\Api\Data\WebhookEventResponseInterface;

class WebhookEventResponse extends \Magento\Framework\Model\AbstractModel implements WebhookEventResponseInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Webhook\Model\ResourceModel\WebhookEventResponse');
    }

    /**
     * @return int
     */
    public function getHistoryId()
    {
        return $this->getData(self::HISTORY_ID);
    }

    /**
     * @param int $historyId
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventResponseInterface
     */
    public function setHistoryId($historyId)
    {
        return $this->setData(self::HISTORY_ID, $historyId);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->getData(self::STATUS_CODE);
    }

    /**
     * @param int $statusCode
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventResponseInterface
     */
    public function setStatusCode($statusCode)
    {
        return $this->setData(self::STATUS_CODE, $statusCode);
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
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventResponseInterface
     */
    public function setBody($body)
    {
        return $this->setData(self::BODY, $body);
    }
}