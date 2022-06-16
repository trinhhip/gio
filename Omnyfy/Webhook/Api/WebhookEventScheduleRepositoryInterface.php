<?php
namespace Omnyfy\Webhook\Api;
use Magento\Framework\Api\SearchCriteriaInterface;
use Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface;

interface WebhookEventScheduleRepositoryInterface
{
    /**
     * @param int $id
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);
    
    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface $webhookEventSchedule
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface
     */
    public function save(WebhookEventScheduleInterface $webhookEventSchedule);

    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface $webhookEventSchedule
     * @return bool Will return True if deleted
     */
    public function delete(WebhookEventScheduleInterface $webhookEventSchedule);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @return \Omnyfy\Webhook\Api\Data\WebhookEventScheduleSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPendingWebhookEvents();
}