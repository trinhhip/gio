<?php
namespace Omnyfy\ProductImport\Model\Config\Source;

class ImageStrategy implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'not_remove', 'label' => __('Do not remove images that are not mentioned')],
            ['value' => 'remove', 'label' => __('Remove images that are not mentioned')]
        ];
    }
}