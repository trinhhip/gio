<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolver;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class SpecialPriceResolver implements CustomAttributeResolverInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    public function execute(ProductInterface $product): ?string
    {
        $price = $product->getPriceInfo()->getPrice(SpecialPrice::PRICE_CODE)->getAmount()->getValue();
        return $price ? $this->priceCurrency->convertAndFormat($price, false) : null;
    }
}
