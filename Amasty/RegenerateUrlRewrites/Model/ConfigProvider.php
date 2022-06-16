<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

/**
 * Scope config Provider model
 */
class ConfigProvider extends ConfigProviderAbstract
{
    protected $pathPrefix = 'amregenerateurlrewrites/';

    const ENABLED = 'general/enabled';
    const SKIP_REINDEX = 'general/skip_reindex';
    const SKIP_CACHE_FLASH = 'general/skip_cache_flash';
    const INCLUDE_CATEGORY_REGENERATION = 'url_rewrites_category/include_category_regeneration';
    const USE_CATEGORY_RANGE_REGENERATE = 'url_rewrites_category/use_category_range_regenerate';
    const CATEGORY_ID_RANGE_REGENERATE = 'url_rewrites_category/category_id_range_regenerate';
    const USE_CATEGORY_IDS_REGENERATE = 'url_rewrites_category/use_category_ids_regenerate';
    const CATEGORY_IDS_REGENERATE = 'url_rewrites_category/category_ids_regenerate';
    const INCLUDE_PRODUCT_REGENERATION = 'url_rewrites_product/include_product_regeneration';
    const USE_PRODUCT_RANGE_REGENERATE = 'url_rewrites_product/use_product_range_regenerate';
    const PRODUCT_ID_RANGE_REGENERATE = 'url_rewrites_product/product_id_range_regenerate';
    const USE_PRODUCT_IDS_REGENERATE = 'url_rewrites_product/use_product_ids_regenerate';
    const PRODUCT_IDS_REGENERATE = 'url_rewrites_product/product_ids_regenerate';
    const APPLY_STORE_VIEWS = 'url_rewrites_regeneration/apply_store_views';

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->getValue(self::ENABLED);
    }

    /**
     * @return bool
     */
    public function isSkipReindex(): bool
    {
        return (bool)$this->getValue(self::SKIP_REINDEX);
    }

    /**
     * @return bool
     */
    public function isSkipCacheFlash(): bool
    {
        return (bool)$this->getValue(self::SKIP_CACHE_FLASH);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isIncludeCategoryRegeneration($storeId = null): bool
    {
        return (bool)$this->getValue(self::INCLUDE_CATEGORY_REGENERATION, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isUseCategoryRangeRegenerate($storeId = null): bool
    {
        return (bool)$this->getValue(self::USE_CATEGORY_RANGE_REGENERATE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCategoryIdRangeRegenerate($storeId = null): string
    {
        return (string)$this->getValue(self::CATEGORY_ID_RANGE_REGENERATE, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isUseCategoryIdsRegenerate($storeId = null): bool
    {
        return (bool)$this->getValue(self::USE_CATEGORY_IDS_REGENERATE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getCategoryIdsRegenerate($storeId = null): string
    {
        return (string)$this->getValue(self::CATEGORY_IDS_REGENERATE, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isIncludeProductRegeneration($storeId = null): bool
    {
        return (bool)$this->getValue(self::INCLUDE_PRODUCT_REGENERATION, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isUseProductRangeRegenerate($storeId = null): bool
    {
        return (bool)$this->getValue(self::USE_PRODUCT_RANGE_REGENERATE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getProductIdRangeRegenerate($storeId = null): string
    {
        return (string)$this->getValue(self::PRODUCT_ID_RANGE_REGENERATE, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isUseProductIdsRegenerate($storeId = null): bool
    {
        return (bool)$this->getValue(self::USE_PRODUCT_IDS_REGENERATE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getProductIdsRegenerate($storeId = null): string
    {
        return (string)$this->getValue(self::PRODUCT_IDS_REGENERATE, $storeId);
    }

    /**
     * @return ?string
     */
    public function getStoreViewsForApply(): ?string
    {
        $storeView = $this->getValue(self::APPLY_STORE_VIEWS);
        return $storeView ? (string)$storeView : null;
    }
}
