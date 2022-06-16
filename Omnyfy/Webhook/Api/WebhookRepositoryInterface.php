<?php
namespace Omnyfy\Webhook\Api;
use Magento\Framework\Api\SearchCriteriaInterface;
use Omnyfy\Webhook\Api\Data\WebhookInterface;

interface WebhookRepositoryInterface
{
    /**
     * @param int $id
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);
    
    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookInterface $webhook
     * @return \Omnyfy\Webhook\Api\Data\WebhookInterface
     */
    public function save(WebhookInterface $webhook);

    /**
     * @param \Omnyfy\Webhook\Api\Data\WebhookInterface $webhook
     * @return bool Will return True if deleted
     */
    public function delete(WebhookInterface $webhook);

    /**
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);

    /**
     * @param int $storeid
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Omnyfy\Webhook\Api\Data\WebhookSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList($storeid, SearchCriteriaInterface $searchCriteria);
}