<?php
namespace Omnyfy\Webhook\Api;
use Magento\Framework\Api\SearchCriteriaInterface;
use Omnyfy\Webhook\Api\Data\WebhookInterface;

interface WebhookTypeRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Omnyfy\Webhook\Api\Data\WebhookTypeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}