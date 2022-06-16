<?php

namespace OmnyfyCustomzation\CmsBlog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ToolTemplate implements ArrayInterface
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }

    /**
     * Retrieve status options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Tool')],
            ['value' => 2, 'label' => __('Template')]
        ];
    }
}
