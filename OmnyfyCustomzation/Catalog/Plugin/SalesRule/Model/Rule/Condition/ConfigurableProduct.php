<?php

namespace OmnyfyCustomzation\Catalog\Plugin\SalesRule\Model\Rule\Condition;

/**
 * Class ConfigurableProduct
 *
 * @package OmnyfyCustomzation\Catalog\Plugin\SalesRule\Model\Rule\Condition
 */
class ConfigurableProduct
{
    /**
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $subject
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return array
     */
    public function beforeValidate(
        \Magento\SalesRule\Model\Rule\Condition\Product $subject,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        $product = $this->getProductToValidate($subject, $model);
        if ($model->getProduct() !== $product) {
            // We need to replace product only for validation and keep original product for all other cases.
            $clone = clone $model;
            $clone->setProduct($product);
            $model = $clone;
        }

        return [$model];
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $subject
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    protected function getProductToValidate(
        \Magento\SalesRule\Model\Rule\Condition\Product $subject,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $model->getProduct();

        $attrCode = $subject->getAttribute();

        /* Check for attributes which are not available for configurable products */
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            && !$product->hasData($attrCode)
            && count($model->getChildren())
        ) {
            /** @var \Magento\Catalog\Model\AbstractModel $childProduct */
            $childProduct = current($model->getChildren())->getProduct();
            if ($childProduct->hasData($attrCode)) {
                $product = $childProduct;
            }
        }

        return $product;
    }
}