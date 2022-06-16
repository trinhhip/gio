<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Ui\DataProvider\Product\Filter;

use Amasty\AltTagGenerator\Model\Template\Condition;
use Amasty\AltTagGenerator\Model\Template\ConditionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Zend\Uri\Uri as ZendUri;

class RuleConditionFilter implements AddFilterToCollectionInterface
{
    const MATCHED_FLAG = 'matched_products';

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Zend_Uri
     */
    private $zendUri;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    public function __construct(
        Json $jsonSerializer,
        ZendUri $zendUri,
        StoreManagerInterface $storeManager,
        ConditionFactory $conditionFactory
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->zendUri = $zendUri;
        $this->storeManager = $storeManager;
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ProductCollection|Collection $collection
     * @param string $field
     * @param null $condition
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $matchedProductIds = $this->getMatchedProducts($condition['eq']);

        if ($matchedProductIds) {
            $collection->addIdFilter($matchedProductIds);
        } else {
            $collection->getSelect()->where('null');
        }

        $collection->setFlag(self::MATCHED_FLAG, $matchedProductIds);
    }

    private function getMatchedProducts(string $queryCondition): array
    {
        $conditions = $this->parseQueryToArray($queryCondition);
        /** @var Condition $ruleCondition */
        $ruleCondition = $this->conditionFactory->create();
        $ruleCondition->setStores([Store::DEFAULT_STORE_ID]);
        $ruleCondition->loadPost($conditions['rule'] ?? []);

        return array_keys($ruleCondition->getMatchingProductIdsForRule());
    }

    private function parseQueryToArray(string $query): array
    {
        $this->zendUri->setQuery($query);
        return $this->zendUri->getQueryAsArray();
    }
}
