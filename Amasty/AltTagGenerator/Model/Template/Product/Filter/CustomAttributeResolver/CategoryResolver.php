<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolver;

use Amasty\AltTagGenerator\Model\ResourceModel\Category\LoadCategories;
use Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class CategoryResolver implements CustomAttributeResolverInterface
{
    /**
     * @var LoadCategories
     */
    private $loadCategories;

    public function __construct(LoadCategories $loadCategories)
    {
        $this->loadCategories = $loadCategories;
    }

    /**
     * @param ProductInterface|Product $product
     * @return string
     */
    public function execute(ProductInterface $product): string
    {
        if ($product->getCategory()) {
            $category = $product->getCategory();
        } else {
            $categories = $this->loadCategories->execute($product->getCategoryIds(), 1); // load deepest category
            $category = array_shift($categories);
        }

        return $category ? $category->getName() : '';
    }
}
