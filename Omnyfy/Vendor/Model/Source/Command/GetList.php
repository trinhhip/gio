<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Vendor\Model\Source\Command;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\ResourceModel\Source\Collection;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterfaceFactory;
use Magento\Backend\Model\Session;

class GetList extends \Magento\Inventory\Model\Source\Command\GetList
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private $sourceCollectionFactory;

    /**
     * @var SourceSearchResultsInterfaceFactory
     */
    private $sourceSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $sourceCollectionFactory
     * @param SourceSearchResultsInterfaceFactory $sourceSearchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $sourceCollectionFactory,
        SourceSearchResultsInterfaceFactory $sourceSearchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Session $session
    ) {
        parent::__construct($collectionProcessor, $sourceCollectionFactory, $sourceSearchResultsFactory, $searchCriteriaBuilder);
        $this->collectionProcessor = $collectionProcessor;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->sourceSearchResultsFactory = $sourceSearchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria = null): SourceSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->sourceCollectionFactory->create();
        $vendorInfo = $this->session->getVendorInfo();
        if (!empty($vendorInfo)) {
            $collection->addFieldToFilter('vendor_id', $vendorInfo['vendor_id']);
        }

        if (null === $searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }

        /** @var SourceSearchResultsInterface $searchResult */
        $searchResult = $this->sourceSearchResultsFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);
        return $searchResult;
    }
}
