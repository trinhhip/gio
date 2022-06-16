<?php
namespace Omnyfy\Webhook\Api\Data;

interface WebhookTypeInterface
{
    const TYPE = 'type';

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
     * @return string
     */
    public function getType();
 
    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);
}