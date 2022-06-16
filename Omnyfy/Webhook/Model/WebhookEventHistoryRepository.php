<?php
namespace Omnyfy\Webhook\Model;

use Omnyfy\Webhook\Api\Data\WebhookEventHistoryInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class WebhookEventHistoryRepository implements \Omnyfy\Webhook\Api\WebhookEventHistoryRepositoryInterface
{
    /**
     * @var \Omnyfy\Webhook\Model\WebhookEventHistoryFactory
     */
    protected $historyFactory;

    /**
     * @var \Omnyfy\Webhook\Model\ResourceModel\WebhookEventHistory\CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Omnyfy\Webhook\Api\Data\WebhookEventHistorySearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * Construct
     *
     * @param \Omnyfy\Webhook\Model\WebhookEventHistoryFactory $historyFactory
     * @param \Omnyfy\Webhook\Model\ResourceModel\WebhookEventHistory\CollectionFactory $historyCollectionFactory
     * @param \Omnyfy\Webhook\Api\Data\WebhookEventHistorySearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Omnyfy\Webhook\Model\WebhookEventHistoryFactory $historyFactory,
        \Omnyfy\Webhook\Model\ResourceModel\WebhookEventHistory\CollectionFactory $historyCollectionFactory,
        \Omnyfy\Webhook\Api\Data\WebhookEventHistorySearchResultsInterfaceFactory $searchResultsFactory
    ){
        $this->historyFactory = $historyFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $history = $this->historyFactory->create();
        $history->load($id);
        if (!$history->getId()) {
            throw new NoSuchEntityException(__('Webhook Event History with id "%1" does not exist.', $id));
        }
        return $history;
    }
    
    /**
     * {@inheritdoc}
     */
    public function save(WebhookEventHistoryInterface $webhookEventHistory)
    {
        try {
            $webhookEventHistory->getResource()->save($webhookEventHistory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the webhook event history: %1',
                $exception->getMessage()
            ));
        }
        return $webhookEventHistory;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WebhookEventHistoryInterface $webhookEventHistory)
    {
        try {
            $webhookEventHistory->getResource()->delete($webhookEventHistory);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the webhook event history: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->historyCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }

        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getListByWebhookId($webhookId)
    {
        $collection = $this->historyCollectionFactory->create();
        $collection->addFieldToFilter('webhook_id', $webhookId);
        $collection->setPageSize(10);

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}