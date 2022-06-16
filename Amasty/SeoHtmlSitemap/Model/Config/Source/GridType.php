<?php

namespace Amasty\SeoHtmlSitemap\Model\Config\Source;

class GridType implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_TREE = 1;
    const TYPE_LIST = 2;

    public function toOptionArray()
    {
        $data = [
            ['value' => self::TYPE_TREE, 'label' => __('Tree')],
            ['value' => self::TYPE_LIST, 'label' => __('List')]
        ];

        return $data;
    }
}