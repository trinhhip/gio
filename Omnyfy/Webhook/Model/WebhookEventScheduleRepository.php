<?php
namespace Omnyfy\Webhook\Model;

use Omnyfy\Webhook\Api\Data\WebhookEventScheduleInterface;
use Magento\Framework\Api\SortOrder;
use Omnyfy\Webhook\Model\WebhookEventSchedule;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class WebhookEventScheduleRepository implements \Omnyfy\Webhook\Api\WebhookEventScheduleRepositoryInterface
{
    /**
     * @var \Omnyfy\Webhook\Model\WebhookEventScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var \Omnyfy\Webhook\Model\ResourceModel\WebhookEventSchedule\CollectionFactory
     */
    protected $scheduleCollectionFactory;

    /**
     * @var \Omnyfy\Webhook\Api\Data\WebhookEventScheduleSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * Construct
     *
     * @param \Omnyfy\Webhook\Model\WebhookEventScheduleFactory $scheduleFactory
     * @param \Omnyfy\Webhook\Model\ResourceModel\WebhookEventSchedule\CollectionFactory $scheduleCollectionFactory
     * @param \Omnyfy\Webhook\Api\Data\WebhookEventScheduleSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Omnyfy\Webhook\Model\WebhookEventScheduleFactory $scheduleFactory,
        \Omnyfy\Webhook\Model\ResourceModel\WebhookEventSchedule\CollectionFactory $scheduleCollectionFactory,
        \Omnyfy\Webhook\Api\Data\WebhookEventScheduleSearchResultsInterfaceFactory $searchResultsFactory

    ){
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id){
        $schedule = $this->scheduleFactory->create();
        $schedule->load($id);
        if (!$schedule->getId()) {
            throw new NoSuchEntityException(__('Webhook Event Schedule with id "%1" does not exist.', $id));
        }
        return $schedule;
    }
    
    /**
     * {@inheritdoc}
     */
    public function save(WebhookEventScheduleInterface $webhookEventSchedule){
        try {
            $webhookEventSchedule->getResource()->save($webhookEventSchedule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the webhook event schedule: %1',
                $exception->getMessage()
            ));
        }
        return $webhookEventSchedule;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WebhookEventScheduleInterface $webhookEventSchedule)
    {
        try {
            $webhookEventSchedule->getResource()->delete($webhookEventSchedule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the webhook event schedule: %1',
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
        $collection = $this->scheduleCollectionFactory->create();
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
    public function getPendingWebhookEvents()
    {
        $collection = $this->scheduleCollectionFactory->create();
        $collection->addFieldToFilter('status', WebhookEventSchedule::STATUS_PENDING);
        $collection->setPageSize(10);

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}