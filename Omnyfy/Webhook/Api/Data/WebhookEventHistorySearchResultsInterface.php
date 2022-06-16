<?php
namespace Omnyfy\Webhook\Api\Data;

interface WebhookEventHistorySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface[]
     */
    public function getItems();

    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}