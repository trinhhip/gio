<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Store\Model\ScopeInterface;

class RatingSummary implements DataMapperInterface
{
    const FIELD_NAME = 'rating_summary';

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []): array
    {
        /**
         * @var \Magento\Catalog\Model\Product $product
         */
        $product = $this->productFactory->create(['data' => ['entity_id' => $entityId]]);

        $this->reviewFactory->create()->getEntitySummary($product, $storeId);
        return [self::FIELD_NAME => $product->getRatingSummary()->getRatingSummary()];
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->scopeConfig->isSetFlag('amshopby/rating_filter/enabled', ScopeInterface::SCOPE_STORE);
    }

    public function getFieldName(): string
    {
        return self::FIELD_NAME;
    }
}
