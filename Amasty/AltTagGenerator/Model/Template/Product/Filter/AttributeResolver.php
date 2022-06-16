<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Psr\Log\LoggerInterface;

class AttributeResolver implements AttributeResolverInterface
{
    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ProductResource $productResource,
        PriceCurrencyInterface $priceCurrency,
        LoggerInterface $logger
    ) {
        $this->productResource = $productResource;
        $this->priceCurrency = $priceCurrency;
        $this->logger = $logger;
    }

    /**
     * @param ProductInterface|Product $product
     * @param string $attributeCode
     * @return string|null
     */
    public function execute(ProductInterface $product, string $attributeCode): ?string
    {
        $result = '';
        if ($value = $product->getData($attributeCode)) {
            try {
                $attribute = $this->productResource->getAttribute($attributeCode);
                if ($attribute) {
                    $result = $attribute->getFrontend()->getValue($product);
                    if ($attribute->getFrontendInput() == 'price' && is_string($result)) {
                        $result = $this->priceCurrency->convertAndFormat($result, false);
                    }
                }
            } catch (LocalizedException $e) {
                $this->logger->warning($e->getMessage());
            }
        }

        return $result ? (string) $result : null;
    }
}
