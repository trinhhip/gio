<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolver;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class IncrementResolver implements CustomAttributeResolverInterface
{
    /**
     * @var array
     */
    private $increment = [];

    public function execute(ProductInterface $product): string
    {
        return (string) $this->getIncrement((int) $product->getId());
    }

    public function clear(): void
    {
        $this->increment = [];
    }

    private function getIncrement(int $productId): int
    {
        if (!isset($this->increment[$productId])) {
            $this->increment[$productId] = 0;
        }

        return ++$this->increment[$productId];
    }
}
