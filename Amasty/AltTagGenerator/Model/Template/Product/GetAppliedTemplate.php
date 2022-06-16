<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\TemplateIndex;
use Amasty\AltTagGenerator\Model\Source\Status;
use Amasty\AltTagGenerator\Model\Template\Query\GetListInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class GetAppliedTemplate
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var TemplateIndex
     */
    private $templateIndex;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var GetListInterface
     */
    private $getList;

    public function __construct(
        TemplateIndex $templateIndex,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        GetListInterface $getList
    ) {
        $this->templateIndex = $templateIndex;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->getList = $getList;
    }

    public function execute(int $storeId, int $productId): ?int
    {
        if (!isset($this->cache[$storeId][$productId])) {
            $appliedTemplateIds = $this->templateIndex->getAppliedTemplates($productId, $storeId);

            $appliedTemplates = $this->getList->execute($this->getSearchCriteria($appliedTemplateIds));
            $this->cache[$storeId][$productId] = $appliedTemplates ? (int) $appliedTemplates[0]->getId() : null;
        }

        return $this->cache[$storeId][$productId];
    }

    private function getSearchCriteria(array $templateIds): SearchCriteria
    {
        $this->sortOrderBuilder->setField(TemplateInterface::PRIORITY);
        $this->sortOrderBuilder->setAscendingDirection();
        $orderByPriority = $this->sortOrderBuilder->create();

        $this->searchCriteriaBuilder->addFilter(TemplateInterface::ID, $templateIds, 'in');
        $this->searchCriteriaBuilder->addFilter(TemplateInterface::ENABLED, Status::ENABLED, 'eq');
        $this->searchCriteriaBuilder->addSortOrder($orderByPriority);

        $this->searchCriteriaBuilder->setCurrentPage(1);
        $this->searchCriteriaBuilder->setPageSize(1);

        return $this->searchCriteriaBuilder->create();
    }
}
