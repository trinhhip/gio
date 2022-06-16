<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolver;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class FinalPriceInclTaxResolver implements CustomAttributeResolverInterface
{
    /**
     * @var CatalogData
     */
    private $catalogData;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(CatalogData $catalogData, PriceCurrencyInterface $priceCurrency)
    {
        $this->catalogData = $catalogData;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param ProductInterface|Product $product
     * @return string
     */
    public function execute(ProductInterface $product): string
    {
        return $this->priceCurrency->convertAndFormat(
            $this->catalogData->getTaxPrice($product, $product->getFinalPrice(), true),
            false
        );
    }
}
