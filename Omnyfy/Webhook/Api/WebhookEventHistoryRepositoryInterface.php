<?php
namespace Omnyfy\Webhook\Api;
use Magento\Framework\Api\SearchCriteriaInterface;
use Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface;

interface WebhookEventHistoryRepositoryInterface
{
    /**
     * @param int $id
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);
    
    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface $webhookEventHistory
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface
     */
    public function save(WebhookEventHistoryInterface $webhookEventHistory);

    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface $webhookEventHistory
     * @return bool Will return True if deleted
     */
    public function delete(WebhookEventHistoryInterface $webhookEventHistory);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventHistorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $webhookId
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventHistorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListByWebhookId($webhookId);
}