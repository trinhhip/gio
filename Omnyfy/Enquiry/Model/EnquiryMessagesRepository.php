<?php


namespace Omnyfy\Enquiry\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Omnyfy\Enquiry\Api\Data\EnquiryMessagesSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Omnyfy\Enquiry\Api\EnquiryMessagesRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages\CollectionFactory as EnquiryMessagesCollectionFactory;
use Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages as ResourceEnquiryMessages;
use Magento\Framework\Exception\CouldNotDeleteException;

class EnquiryMessagesRepository implements enquiryMessagesRepositoryInterface
{

    protected $dataObjectProcessor;

    private $storeManager;

    protected $dataEnquiryMessagesFactory;

    protected $dataObjectHelper;

    protected $resource;

    protected $searchResultsFactory;

    protected $enquiryMessagesFactory;

    protected $enquiryMessagesCollectionFactory;


    /**
     * @param ResourceEnquiryMessages $resource
     * @param EnquiryMessagesFactory $enquiryMessagesFactory
     * @param EnquiryMessagesInterfaceFactory $dataEnquiryMessagesFactory
     * @param EnquiryMessagesCollectionFactory $enquiryMessagesCollectionFactory
     * @param EnquiryMessagesSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceEnquiryMessages $resource,
        EnquiryMessagesFactory $enquiryMessagesFactory,
        EnquiryMessagesInterfaceFactory $dataEnquiryMessagesFactory,
        EnquiryMessagesCollectionFactory $enquiryMessagesCollectionFactory,
        EnquiryMessagesSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->enquiryMessagesFactory = $enquiryMessagesFactory;
        $this->enquiryMessagesCollectionFactory = $enquiryMessagesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataEnquiryMessagesFactory = $dataEnquiryMessagesFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface $enquiryMessages
    ) {
        /* if (empty($enquiryMessages->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $enquiryMessages->setStoreId($storeId);
        } */
        try {
            $enquiryMessages->getResource()->save($enquiryMessages);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the enquiryMessages: %1',
                $exception->getMessage()
            ));
        }
        return $enquiryMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($enquiryMessagesId)
    {
        $enquiryMessages = $this->enquiryMessagesFactory->create();
        $enquiryMessages->getResource()->load($enquiryMessages, $enquiryMessagesId);
        if (!$enquiryMessages->getId()) {
            throw new NoSuchEntityException(__('enquiry_messages with id "%1" does not exist.', $enquiryMessagesId));
        }
        return $enquiryMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->enquiryMessagesCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
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
    public function delete(
        \Omnyfy\Enquiry\Api\Data\EnquiryMessagesInterface $enquiryMessages
    ) {
        try {
            $enquiryMessages->getResource()->delete($enquiryMessages);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the enquiry_messages: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($enquiryMessagesId)
    {
        return $this->delete($this->getById($enquiryMessagesId));
    }
}
