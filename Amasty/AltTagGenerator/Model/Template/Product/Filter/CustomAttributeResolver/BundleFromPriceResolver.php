<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolver;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class BundleFromPriceResolver implements CustomAttributeResolverInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param ProductInterface|Product $product
     * @return string
     */
    public function execute(ProductInterface $product): string
    {
        return $this->priceCurrency->convertAndFormat(
            $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue(),
            false
        );
    }
}
