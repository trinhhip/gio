<?php

namespace OmnyfyCustomzation\CmsBlog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class LinkType implements ArrayInterface
{
    /**
     * Retrieve status options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('URL')],
            ['value' => 0, 'label' => __('Document')]
        ];
    }
}
