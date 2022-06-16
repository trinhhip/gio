<?php
namespace Omnyfy\Webhook\Api\Data;

interface WebhookSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface[]
     */
    public function getItems();

    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}