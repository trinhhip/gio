<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter;

use Magento\Catalog\Api\Data\ProductInterface;

interface CustomAttributeResolverInterface
{
    public function execute(ProductInterface $product): ?string;
}
