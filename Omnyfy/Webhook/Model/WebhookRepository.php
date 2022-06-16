<?php
namespace Omnyfy\Webhook\Model;

use Omnyfy\Webhook\Api\Data\WebhookInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

class WebhookRepository implements \Omnyfy\Webhook\Api\WebhookRepositoryInterface
{

    /**
     * @var \Omnyfy\Webhook\Model\WebhookFactory
     */
    protected $webhookFactory;

    /**
     * @var \Omnyfy\Webhook\Model\ResourceModel\Webhook\CollectionFactory
     */
    protected $webhookCollectionFactory;

    /**
     * @var \Omnyfy\Webhook\Api\Data\WebhookSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * Construct
     *
     * @param \Omnyfy\Webhook\Model\WebhookFactory $webhookFactory
     * @param \Omnyfy\Webhook\Model\ResourceModel\Webhook\CollectionFactory $webhookCollectionFactory
     * @param \Omnyfy\Webhook\Api\Data\WebhookSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Omnyfy\Webhook\Model\WebhookFactory $webhookFactory,
        \Omnyfy\Webhook\Model\ResourceModel\Webhook\CollectionFactory $webhookCollectionFactory,
        \Omnyfy\Webhook\Api\Data\WebhookSearchResultsInterfaceFactory $searchResultsFactory
    ){
        $this->webhookFactory = $webhookFactory;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $webhook = $this->webhookFactory->create();
        $webhook->load($id);
        if (!$webhook->getId()) {
            throw new NoSuchEntityException(__('Webhook with id "%1" does not exist.', $id));
        }
        return $webhook;
    }
    
    /**
     * {@inheritdoc}
     */
    public function save(WebhookInterface $webhook)
    {
        try {
            $webhook->getResource()->save($webhook);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the webhook: %1',
                $exception->getMessage()
            ));
        }
        return $webhook;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WebhookInterface $webhook)
    {
        try {
            $webhook->getResource()->delete($webhook);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the webhook: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * {@inheritdoc}
     */
    public function getList($storeid, \Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->webhookCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
            $collection->addFieldToFilter('store_id', $storeid);
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
}
