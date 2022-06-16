<?php
namespace Omnyfy\Webhook\Model;
use Magento\Framework\Api\SortOrder;

class WebhookTypeRepository implements \Omnyfy\Webhook\Api\WebhookTypeRepositoryInterface
{
    /**
     * @var \Omnyfy\Webhook\Model\ResourceModel\WebhookType\CollectionFactory
     */
    protected $webhookTypeCollectionFactory;

    /**
     * @var \Omnyfy\Webhook\Api\Data\WebhookTypeSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * Construct
     *
     * @param \Omnyfy\Webhook\Model\ResourceModel\WebhookType\CollectionFactory $webhookTypeCollectionFactory
     * @param \Omnyfy\Webhook\Api\Data\WebhookTypeSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Omnyfy\Webhook\Model\ResourceModel\WebhookType\CollectionFactory $webhookTypeCollectionFactory,
        \Omnyfy\Webhook\Api\Data\WebhookTypeSearchResultsInterfaceFactory $searchResultsFactory
    ){
        $this->webhookTypeCollectionFactory = $webhookTypeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->webhookTypeCollectionFactory->create();
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
}