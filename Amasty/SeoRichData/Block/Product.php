<?php

namespace Amasty\SeoRichData\Block;

use Amasty\SeoRichData\Helper\Config as ConfigHelper;
use Amasty\SeoRichData\Model\ConfigProvider;
use Amasty\SeoRichData\Model\Source\Product\Description as DescriptionSource;
use Amasty\SeoRichData\Model\Source\Product\OfferItemCondition as OfferItemConditionSource;
use Amasty\SeoRichData\Model\Source\Product\RatingFormat;
use DateTime;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

class Product extends AbstractBlock
{
    const IN_STOCK = 'http://schema.org/InStock';

    const OUT_OF_STOCK = 'http://schema.org/OutOfStock';

    const MPN_IDENTIFIER = 'mpn';
    const SKU_IDENTIFIER = 'sku';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $pageConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Amasty\SeoRichData\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    private $reviewCollectionFactory;

    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    private $ratingFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var OfferItemConditionSource
     */
    private $offerItemConditionSource;

    /**
     * @var ProductResource
     */
    private $productResource;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        ConfigHelper $configHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        ConfigProvider $configProvider,
        OfferItemConditionSource $offerItemConditionSource,
        ProductResource $productResource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->pageConfig = $pageConfig;
        $this->storeManager = $storeManager;
        $this->stockRegistry = $stockRegistry;
        $this->configHelper = $configHelper;
        $this->imageHelper = $imageHelper;
        $this->dateTime = $dateTime;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->ratingFactory = $ratingFactory;
        $this->configProvider = $configProvider;
        $this->offerItemConditionSource = $offerItemConditionSource;
        $this->productResource = $productResource;
    }

    protected function _toHtml()
    {
        if (!$this->configHelper->forProductEnabled()) {
            return '';
        }

        $resultArray = $this->getResultArray();
        $json = json_encode($resultArray);
        $result = "<script type=\"application/ld+json\">{$json}</script>";

        return $result;
    }

    /**
     * @return array
     */
    public function getResultArray()
    {
        /** @var ProductModel $product */
        $product = $this->getProduct();

        if (!$product) {
            $product = $this->coreRegistry->registry('current_product');
        }

        $offers = $this->prepareOffers($product);
        $offers = $this->unsetUnnecessaryData($offers);
        $rating = $this->getRating($product);
        $reviews = $this->getReviews($product);
        $image = $this->imageHelper->init(
            $product,
            'product_page_image_medium_no_frame',
            ['type' => 'image']
        )->getUrl();
        $resultArray = [
            '@context' => 'http://schema.org',
            '@type' => 'Product',
            'name' => $product->getName(),
            'description' => $this->stripTags($this->getProductDescription($product)),
            'image' => $image,
            'aggregateRating' => $rating,
            'review' => $reviews,
            'offers' => $offers,
            'url' => $product->getProductUrl()
        ];

        if ($brandInfo = $this->getBrandInfo($product)) {
            $resultArray['brand'] = $brandInfo;
        }

        if ($manufacturerInfo = $this->getManufacturerInfo($product)) {
            $resultArray['manufacturer'] = $manufacturerInfo;
        }

        $this->updateCustomProperties($resultArray, $product);

        return $resultArray;
    }

    protected function prepareOffers($product)
    {
        $offers = [];
        $priceCurrency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $orgName = $this->storeManager->getStore()->getFrontendName();
        $productType = $product->getTypeId();

        switch ($productType) {
            case ConfigurableType::TYPE_CODE:
            case GroupedType::TYPE_CODE:
                if ($this->configHelper->showAggregate($productType)) {
                    $offers[] = $this->generateAggregateOffers(
                        $this->getSimpleProducts($product),
                        $priceCurrency
                    );
                } elseif ($this->configHelper->showAsList($productType)) {
                    foreach ($this->getSimpleProducts($product) as $child) {
                        $offers[] = $this->generateOffers($child, $priceCurrency, $orgName, $product);
                    }
                } else {
                    $offers[] = $this->generateOffers($product, $priceCurrency, $orgName);
                }
                break;
            default:
                $offers[] = $this->generateOffers($product, $priceCurrency, $orgName);
        }

        return $offers;
    }

    /**
     * @param ProductModel $product
     *
     * @return array
     */
    private function getSimpleProducts($product)
    {
        $list = [];
        $typeInstance = $product->getTypeInstance();

        switch ($product->getTypeId()) {
            case ConfigurableType::TYPE_CODE:
                $list = $typeInstance->getUsedProducts($product);
                break;
            case GroupedType::TYPE_CODE:
                $list = $typeInstance->getAssociatedProducts($product);
                break;
        }

        return $list;
    }

    /**
     * @param $listOfSimples
     * @param string $priceCurrency
     *
     * @return array
     */
    private function generateAggregateOffers($listOfSimples, $priceCurrency)
    {
        $minPrice = INF;
        $maxPrice = 0;
        $offerCount = 0;

        foreach ($listOfSimples as $child) {
            $childPrice = $child->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            $minPrice = min($minPrice, $childPrice);
            $maxPrice = max($maxPrice, $childPrice);
            $offerCount++;
        }

        return [
            '@type' => 'AggregateOffer',
            'lowPrice' => round($minPrice, 2),
            'highPrice' => round($maxPrice, 2),
            'offerCount' => $offerCount,
            'priceCurrency' => $priceCurrency
        ];
    }

    protected function unsetUnnecessaryData($offers)
    {
        if (!$this->configHelper->showAvailability()) {
            foreach ($offers as $key => $offer) {
                if (isset($offer['availability'])) {
                    unset($offers[$key]['availability']);
                }
            }
        }

        if (!$this->configHelper->showCondition()) {
            foreach ($offers as $key => $offer) {
                if (isset($offer['itemCondition'])) {
                    unset($offers[$key]['itemCondition']);
                }
            }
        }

        return $offers;
    }

    /**
     * @param $product
     * @return array
     */
    protected function getRating($product)
    {
        $rating = [];

        if ($this->configHelper->showRating()) {
            $ratingSummary = $product->getRatingSummary();
            $ratingValue = $ratingSummary['rating_summary'] ?? $ratingSummary;
            $reviewCount = $ratingSummary['reviews_count'] ?? $product->getReviewsCount();

            [$ratingValue, $bestRating] = $this->getRatingData(
                $ratingValue,
                100
            );
            if ($ratingValue && $reviewCount) {
                $rating = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $ratingValue,
                    'bestRating' => $bestRating,
                    'reviewCount' => $reviewCount
                ];
            }
        }

        return $rating;
    }

    protected function generateOffers(
        ProductModel $product,
        string $priceCurrency,
        string $orgName,
        ?ProductModel $parentProduct = null
    ): array {
        if (!in_array(
            $this->getProductVisibility($product),
            [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]
        ) && $parentProduct) {
            $productUrl = $parentProduct->getProductUrl();
        } else {
            $productUrl = $product->getProductUrl();
        }
        $price = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $itemConditionValue = $product->hasData(OfferItemConditionSource::ATTRIBUTE_CODE)
            ? (int) $product->getData(OfferItemConditionSource::ATTRIBUTE_CODE)
            : OfferItemConditionSource::NEW_CONDITION;
        $offers = [
            '@type' => 'Offer',
            'priceCurrency' => $priceCurrency,
            'price' => round($price, 2),
            'availability' => $this->getAvailabilityCondition($product),
            'itemCondition' => $this->offerItemConditionSource->getConditionValue($itemConditionValue),
            'seller' => [
                '@type' => 'Organization',
                'name' => $orgName
            ],
            'url' => $productUrl
        ];

        if ($this->configProvider->isReplacePriceValidUntil()
            && $product->getSpecialPrice()
            && $this->dateTime->timestamp() < $this->dateTime->timestamp($product->getSpecialToDate())
        ) {
            $offers['priceValidUntil'] = $this->dateTime->date(DateTime::ATOM, $product->getSpecialToDate());
        } elseif ($this->configProvider->getDefaultPriceValidUntil()) {
            $offers['priceValidUntil'] = $this->dateTime->date(
                DateTime::ATOM,
                $this->configProvider->getDefaultPriceValidUntil()
            );
        }

        return $offers;
    }

    private function getProductVisibility(ProductModel $product): int
    {
        $visibility = $product->getVisibility();
        if ($visibility === null) {
            $visibility = $this->productResource->getAttributeRawValue(
                $product->getId(),
                ProductInterface::VISIBILITY,
                $this->storeManager->getStore()->getId()
            );
        }

        return (int) $visibility;
    }

    /**
     * @param ProductModel $product
     *
     * @return array|null
     */
    private function getBrandInfo($product)
    {
        $info = null;
        $brand = $this->configHelper->getBrandAttribute();

        if ($brand && $attributeValue = $product->getAttributeText($brand)) {
            $info = [
                '@type' => 'Thing',
                'name' => $attributeValue
            ];
        }

        return $info;
    }

    /**
     * @param ProductModel $product
     *
     * @return array|null
     */
    private function getManufacturerInfo($product)
    {
        $info = null;
        $manufacturer = $this->configHelper->getManufacturerAttribute();

        if ($manufacturer && $attributeValue = $product->getAttributeText($manufacturer)) {
            $info = [
                '@type' => 'Organization',
                'name' => $attributeValue
            ];
        }

        return $info;
    }

    /**
     * @param ProductModel $product
     *
     * @return string
     */
    public function getAvailabilityCondition($product)
    {
        $availabilityCondition = $this->stockRegistry->getProductStockStatus($product->getId())
            ? self::IN_STOCK
            : self::OUT_OF_STOCK;

        return $availabilityCondition;
    }

    /**
     * @param $product
     * @return array
     */
    private function getReviews($product)
    {
        $reviews[] = [];

        if ($this->configHelper->showRating()) {
            $reviewCollection = $this->reviewCollectionFactory->create()->addStoreFilter(
                $this->storeManager->getStore()->getId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $product->getId()
            )->setDateOrder();

            foreach ($reviewCollection as $review) {
                $rating = $this->ratingFactory->create()->getReviewSummary($review->getId(), true);
                [$ratingValue, $bestRating] = $this->getRatingData(
                    $rating->getSum(),
                    $rating->getCount() * 100
                );
                $reviews[] = [
                    '@type' => 'Review',
                    'author' => $review->getNickname(),
                    'datePublished' => $review->getCreatedAt(),
                    'reviewBody' => $review->getDetail(),
                    'name' => $review->getTitle(),
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' =>$ratingValue,
                        'bestRating' => $bestRating
                    ]
                ];
            }
        }

        return $reviews;
    }

    private function getRatingData(?float $ratingValue, float $fromBestRating): array
    {
        if ($ratingValue === null) {
            return [0, 0];
        }

        if ($this->configProvider->getRatingFormat() === RatingFormat::NUMERIC) {
            $bestRating = 5;
            $ratingValue = $this->formatRating($ratingValue, $fromBestRating, 5);
        } else {
            $bestRating = 100;
            $ratingValue = $this->formatRating($ratingValue, $fromBestRating, 100);
        }

        return [$ratingValue, $bestRating];
    }

    private function formatRating(float $value, float $fromBestRating, float $toBestRating): float
    {
        return round($value * $toBestRating / $fromBestRating, 1);
    }

    /**
     * @param array $result
     * @param ProductModel $product
     */
    private function updateCustomProperties(&$result, $product)
    {
        foreach ($this->configHelper->getCustomAttributes() as $pair) {
            $snippetProperty = isset($pair[0]) ? trim($pair[0]) : null;
            $attributeCode = isset($pair[1]) ? trim($pair[1]) : $snippetProperty;

            if ($snippetProperty && $attributeCode) {
                if ($product->getData($attributeCode)) {
                    $result[$snippetProperty] = $product->getAttributeText($attributeCode)
                        ? $product->getAttributeText($attributeCode)
                        : $product->getData($attributeCode);
                }
            }
        }
    }

    /**
     * @param ProductModel $product
     *
     * @return string
     */
    private function getProductDescription($product)
    {
        $description = '';

        switch ($this->configHelper->getProductDescriptionMode()) {
            case DescriptionSource::SHORT_DESCRIPTION:
                $description = $this->getMetaData($product, 'short_description') ?: $product->getShortDescription();
                break;
            case DescriptionSource::FULL_DESCRIPTION:
                $description = $this->getMetaData($product, 'description') ?: $product->getDescription();
                break;
            case DescriptionSource::META_DESCRIPTION:
                $description =  $this->getMetaData($product, 'meta_description')
                    ?: $this->pageConfig->getDescription();
                break;
        }

        return $description;
    }

    /**
     * Value of this method resolved in Amasty_Meta
     *
     * @param ProductModel $product
     * @param string $key
     *
     * @return string
     */
    public function getMetaData($product, $key)
    {
        return '';
    }
}
