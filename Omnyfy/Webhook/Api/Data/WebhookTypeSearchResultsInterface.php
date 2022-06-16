<?php
namespace Omnyfy\Webhook\Api\Data;

interface WebhookTypeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Omnyfy\Webhook\Api\Data\WebhookTypeInterface[]
     */
    public function getItems();

    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookTypeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}