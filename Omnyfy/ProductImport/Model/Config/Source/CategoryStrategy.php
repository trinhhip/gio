<?php
namespace Omnyfy\ProductImport\Model\Config\Source;

class CategoryStrategy implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'not_remove', 'label' => __('Do not remove categories that are not mentioned')],
            ['value' => 'remove', 'label' => __('Remove categories that are not mentioned')]
        ];
    }
}