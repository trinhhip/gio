<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Query;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\Collection;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\CollectionFactory;
use Amasty\AltTagGenerator\Model\Template\Registry;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;

class GetList implements GetListInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        CollectionFactory $collectionFactory,
        ReadExtensions $readExtensions,
        Registry $registry
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->readExtensions = $readExtensions;
        $this->registry = $registry;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TemplateInterface[]
     */
    public function execute(SearchCriteriaInterface $searchCriteria): array
    {
        /** @var Collection $ */
        $collection = $this->collectionFactory->create();

        $this->addFilterGroupToCollection($collection, $searchCriteria->getFilterGroups());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($collection, $sortOrders);
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $templates = [];
        /** @var TemplateInterface $template */
        foreach ($collection->getItems() as $template) {
            $templateInMemory = $this->registry->get((int) $template->getId());
            if ($templateInMemory) {
                $template = $templateInMemory;
            } else {
                $this->readExtensions->execute($template);
                $this->registry->save($template);
            }
            $templates[] = $template;
        }

        return $templates;
    }

    /**
     * @param Collection $collection
     * @param FilterGroup[] $filterGroups
     * @return void
     */
    private function addFilterGroupToCollection(Collection $collection, array $filterGroups): void
    {
        foreach ($filterGroups as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
    }

    /**
     * @param Collection $collection
     * @param SortOrder[] $sortOrders
     * @return void
     */
    private function addOrderToCollection(Collection $collection, array $sortOrders): void
    {
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
