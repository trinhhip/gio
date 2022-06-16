<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Model;

use Amasty\Groupcat\Api\Data\RuleInterface;
use Amasty\Groupcat\Model\Rule\PriceActionOptionsProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Amasty\Base\Model\Serializer;

/**
 * Provide and Store Rules for products and categories
 */
class ProductRuleProvider
{
    /**
     * @var ResourceModel\Rule
     */
    private $ruleResource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var ResourceModel\Rule\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Framework\App\Cache\Type\Collection
     */
    private $collectionCache;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    private $categoryResource;

    /**
     * Cached restricted category IDs
     *
     * @var array|null
     */
    protected static $restrictedCategories = null;

    /**
     * Cached restricted product IDs
     *
     * @var array|null
     */
    protected static $restrictedProducts = null;

    /**
     * Cached data of product rules
     *
     * @var array
     */
    protected static $rulesData = [];

    /**
     * @var int
     */
    private $customerId;

    /**
     * @var CustomerIdHolder
     */
    private $customerIdHolder;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        \Amasty\Groupcat\Model\ResourceModel\Rule $ruleResource,
        \Amasty\Groupcat\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\Cache\Type\Collection $collectionCache,
        \Amasty\Groupcat\Model\CustomerIdHolder $customerIdHolder,
        Serializer $serializer
    ) {
        $this->ruleResource = $ruleResource;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->collectionFactory = $collectionFactory;
        $this->collectionCache = $collectionCache;
        $this->categoryResource = $categoryResource;
        $this->customerIdHolder = $customerIdHolder;
        $this->serializer = $serializer;
        $this->customerId = $this->getCustomerId();
    }

    /**
     * get Rules collection filtered by data, store, customer, is_active
     *
     * @return ResourceModel\Rule\Collection
     */
    public function getActiveRulesCollection()
    {
        $store = $this->storeManager->getStore();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $customerId = $this->getCustomerId();
        $date = $this->localeDate->scopeDate($store->getId());
        $collection = $this->collectionFactory->create();
        $collection->addIsActiveFilter()
            ->addStoreFilter($store)
            ->addCustomerGroupFilter($customerGroupId)
            ->addDateInRangeFilter($date)
            ->addCustomerIdFilter($customerId);

        return $collection;
    }

    /**
     * Get Rule Index based on product, date of current store, store_id and current customer_group_id
     *
     * @param ProductInterface|\Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getRuleForProduct(ProductInterface $product)
    {
        $productId = $product->getId();

        if (!array_key_exists($productId, self::$rulesData)) {
            $storeId = $product->getStoreId();
            $dateTs = $this->localeDate->scopeTimeStamp($storeId);

            if ($product->hasCustomerGroupId()) {
                $customerGroupId = $product->getCustomerGroupId();
            } else {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
            }

            self::$rulesData[$productId] = $this->ruleResource->getOneRuleForProduct(
                $dateTs,
                $storeId,
                $customerGroupId,
                $productId,
                $this->getCustomerId()
            );
        }

        return self::$rulesData[$productId];
    }

    /**
     * @param ProductInterface $product
     *
     * @return int
     */
    public function getProductPriceAction(ProductInterface $product)
    {
        $rule = $this->getRuleForProduct($product);

        if (is_array($rule) && array_key_exists(RuleInterface::PRICE_ACTION, $rule)) {
            return $rule[RuleInterface::PRICE_ACTION];
        }

        return PriceActionOptionsProvider::SHOW;
    }

    /**
     * @param ProductInterface $product
     *
     * @return int
     */
    public function getProductIsHideCart(ProductInterface $product)
    {
        $rule = $this->getRuleForProduct($product);

        if (is_array($rule) && array_key_exists(RuleInterface::HIDE_CART, $rule)) {
            return $rule[RuleInterface::HIDE_CART];
        }

        return 0;
    }

    /**
     * @param ProductInterface $product
     *
     * @return int
     */
    public function getProductIsHideWishlist(ProductInterface $product)
    {
        $rule = $this->getRuleForProduct($product);

        if (is_array($rule) && array_key_exists(RuleInterface::HIDE_WISHLIST, $rule)) {
            return $rule[RuleInterface::HIDE_WISHLIST];
        }

        return 0;
    }

    /**
     * @param ProductInterface $product
     *
     * @return int
     */
    public function getProductIsHideCompare(ProductInterface $product)
    {
        $rule = $this->getRuleForProduct($product);

        if (is_array($rule) && array_key_exists(RuleInterface::HIDE_COMPARE, $rule)) {
            return $rule[RuleInterface::HIDE_COMPARE];
        }

        return 0;
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function isProductRestricted(ProductInterface $product)
    {
        $rule = $this->getRuleForProduct($product);

        if ($rule[RuleInterface::HIDE_PRODUCT]) {
            return true;
        }

        return false;
    }

    /**
     * Get array of restricted product ids for current store, store date and customer group
     *
     * @return array|null
     */
    public function getRestrictedProductIds()
    {
        if (self::$restrictedProducts === null) {
            $store = $this->storeManager->getStore();
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerId = $this->getCustomerId();
            $cacheId = __CLASS__ . '_restrictedProducts_store' . $store->getId()
                . 'customer_group' . $customerGroupId . 'customer' . $customerId;
            $productIds = $this->hasCacheData($this->collectionCache->load($cacheId));

            if (!$productIds) {
                $dateTs = $this->localeDate->scopeTimeStamp($store);
                $productIds = $this->ruleResource->getRestrictedProductIds(
                    $dateTs,
                    $store->getId(),
                    $customerGroupId,
                    $customerId
                );
                $this->collectionCache->save(
                    $this->serializer->serialize($productIds),
                    $cacheId,
                    [],
                    3600 // some rules have data range. we should check data range again after 1 hour
                );
            }

            self::$restrictedProducts = $productIds;
        }

        return self::$restrictedProducts;
    }

    /**
     * Get array of restricted category ids for store date and customer group
     *
     * @return array|null
     */
    public function getRestrictCategoriesId()
    {
        if (self::$restrictedCategories === null) {
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $cacheId = __CLASS__ . '_restrictedCategories_customer_group' . $customerGroupId
                . 'customer' . $this->getCustomerId();
            $categories = $this->hasCacheData($this->collectionCache->load($cacheId));

            if (!$categories) {
                $collection = $this->getActiveRulesCollection();
                $collection->addOrder(RuleInterface::PRIORITY, $collection::SORT_ORDER_DESC);
                $categories = $this->ruleResource->getCategoryIdsFromCollection($collection);
                $this->collectionCache->save(
                    $this->serializer->serialize($categories),
                    $cacheId,
                    [],
                    3600 // some rules have data range. we should check data range again after 1 hour
                );
            }

            self::$restrictedCategories = $categories;
        }

        return self::$restrictedCategories;
    }

    /**
     * Check full path of current category.
     *
     * @param int $categoryId
     *
     * @return ResourceModel\Rule\Collection
     */
    public function getRulesForCategoryView($categoryId)
    {
        $categoryPath = $this->categoryResource->getCategoryPathById($categoryId);
        $categoryIds = explode('/', $categoryPath);
        $collection = $this->getActiveRulesCollection();
        $collection->addCategoryFilter($categoryIds)
            ->addOrder(RuleInterface::PRIORITY, $collection::SORT_ORDER_DESC);

        return $collection;
    }

    /**
     * check and prepare cache for use
     *
     * @param $cachedData
     *
     * @return bool|mixed
     */
    protected function hasCacheData($cachedData)
    {
        if ($cachedData) {
            $cachedData = $this->serializer->unserialize($cachedData);
        }

        if (is_array($cachedData) && count($cachedData)) {
            return $cachedData;
        }

        return false;
    }

    /**
     * Check if we should generate request form instead of price
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function isShowPriceRequest(ProductInterface $product)
    {
        $rule = $this->getRuleForProduct($product);

        if (is_array($rule) && array_key_exists(RuleInterface::PRICE_ACTION, $rule)) {
            return $rule[RuleInterface::PRICE_ACTION] == PriceActionOptionsProvider::REPLACE_REQUEST;
        }

        return false;
    }

    /**
     * Save customer id when caches are enabled
     *
     * @return int
     */
    public function getCustomerId()
    {
        if ($this->customerIdHolder->isIdInitialized()) {
            return $this->customerId = $this->customerIdHolder->getCustomerId();
        }

        if ($this->customerId === null) {
            if ($this->customerSession->getCustomerId() === null) {
                $this->customerSession->start();
            }

            $this->customerId = (int)$this->customerSession->getCustomerId();
        }

        return $this->customerId;
    }
}
