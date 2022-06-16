<?php

namespace Omnyfy\Vendor\Model\Override\InventoryCatalog\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class IsSingleSourceMode extends \Magento\InventoryCatalog\Model\IsSingleSourceMode
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($sourceRepository, $searchCriteriaBuilder);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceInterface::ENABLED, true)
            ->create();

        $searchResult = $this->sourceRepository->getList($searchCriteria);
        return false;
    }
}
