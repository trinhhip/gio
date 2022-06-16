<?php
namespace Omnyfy\Webhook\Api\Data;

interface WebhookEventScheduleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface[]
     */
    public function getItems();

    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}