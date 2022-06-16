<?php
namespace Omnyfy\Webhook\Api\Data;

interface WebhookEventResponseInterface
{
    const HISTORY_ID = 'history_id';

    const STATUS_CODE = 'status_code';

    const BODY = 'body';

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
    public function getHistoryId();
 
    /**
     * @param int $historyId
     * @return $this
     */
    public function setHistoryId($historyId);

    /**
     * @return int
     */
    public function getStatusCode();
 
    /**
     * @param int statusCode
     * @return $this
     */
    public function setStatusCode($historyId);

    /**
     * @return string
     */
    public function getBody();

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body);
}