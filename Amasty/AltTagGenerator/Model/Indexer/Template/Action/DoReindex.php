<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Indexer\Template\Action;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Indexer\Template\CacheContext;
use Amasty\AltTagGenerator\Model\ResourceModel\TemplateIndex;
use Amasty\AltTagGenerator\Model\ResourceModel\TemplateIndex\TableWorker;
use Amasty\AltTagGenerator\Model\Source\Status;
use Amasty\AltTagGenerator\Model\Template;
use Amasty\AltTagGenerator\Model\Template\Condition;
use Amasty\AltTagGenerator\Model\Template\ConditionFactory;
use Amasty\AltTagGenerator\Model\Template\Query\GetListInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;

class DoReindex
{
    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var GetListInterface
     */
    private $getList;

    /**
     * @var TableWorker
     */
    private $tableWorker;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * @var int
     */
    private $batchCacheCount;

    public function __construct(
        ConditionFactory $conditionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        GetListInterface $getList,
        TableWorker $tableWorker,
        CacheContext $cacheContext,
        ManagerInterface $eventManager,
        int $batchCount = 1000,
        int $batchCacheCount = 100
    ) {
        $this->conditionFactory = $conditionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->getList = $getList;
        $this->tableWorker = $tableWorker;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->batchCount = $batchCount;
        $this->batchCacheCount = $batchCacheCount;
    }

    public function execute(?array $templateIds = null, ?array $productIds = null): void
    {
        $rows = [];
        $count = 0;

        /** @var Condition $ruleCondition */
        $ruleCondition = $this->conditionFactory->create();
        foreach ($this->getTemplates($templateIds) as $template) {
            if ($template->getExtensionAttributes()->getStores() && $template->getConditionsSerialized()) {
                $ruleCondition->clearResult();

                if ($productIds !== null) {
                    $ruleCondition->setProductsFilter($productIds);
                }
                $ruleCondition->setStores($template->getExtensionAttributes()->getStores());
                $ruleCondition->setConditionsSerialized($template->getConditionsSerialized());

                $matchedProducts = $ruleCondition->getMatchingProductIdsForRule();
                foreach ($matchedProducts as $productId => $storeIds) {
                    while ($storeIds) {
                        $rows[] = [
                            TemplateIndex::PRODUCT_ID => $productId,
                            TemplateIndex::STORE_ID => array_shift($storeIds),
                            TemplateIndex::TEMPLATE_ID => $template->getId()
                        ];
                        if (++$count > $this->batchCount) {
                            $this->tableWorker->insert($rows);
                            $count = 0;
                            $rows = [];
                        }
                    }
                    $this->registerEntities(Product::CACHE_TAG, [$productId]);
                }
                $this->registerEntities(Template::CACHE_TAG, [$template->getId()]);
            }
        }
        $this->tableWorker->insert($rows);
        $this->cleanCache();
    }

    /**
     * @param array|null $ids
     * @return TemplateInterface[]
     */
    private function getTemplates(?array $ids): array
    {
        $filters = [];

        if ($ids !== null) {
            $filters[] = $this->filterBuilder->setField(TemplateInterface::ID)
                ->setValue($ids)
                ->setConditionType('in')
                ->create();
        }
        $filters[] = $this->filterBuilder->setField(TemplateInterface::ENABLED)
            ->setValue(Status::ENABLED)
            ->setConditionType('eq')
            ->create();

        $this->searchCriteriaBuilder->addFilters($filters);

        return $this->getList->execute($this->searchCriteriaBuilder->create());
    }

    protected function registerEntities($cacheTag, $ids)
    {
        $this->cacheContext->registerEntities($cacheTag, $ids);
        if ($this->cacheContext->getSize() > $this->batchCacheCount) {
            $this->cleanCache();
            $this->cacheContext->flush();
        }
    }

    protected function cleanCache()
    {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}
