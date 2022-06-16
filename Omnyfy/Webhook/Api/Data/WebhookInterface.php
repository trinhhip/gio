<?php
namespace Omnyfy\Webhook\Api\Data;

interface WebhookInterface
{
    const STATUS = 'status';

    const STORE_ID = 'store_id';

    const WEBHOOK_TYPE_ID = 'webhook_type_id';

    const METHOD = 'method';

    const ENDPOINT_URL = 'endpoint_url';

    const CONTENT_TYPE = 'content_type';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

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
    public function getStatus();
 
    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getStoreId();
 
    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return int
     */
    public function getWebhookTypeId();
 
    /**
     * @param int $webhookTypeId
     * @return $this
     */
    public function setWebhookTypeId($webhookTypeId);

    /**
     * @return string
     */
    public function getMethod();
 
    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * @return string
     */
    public function getEndpointUrl();
 
    /**
     * @param string $endpointUrl
     * @return $this
     */
    public function setEndpointUrl($endpointUrl);

    /**
     * @return string
     */
    public function getContentType();
 
    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType($contentType);

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
}
