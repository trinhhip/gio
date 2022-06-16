<?php 

namespace Omnyfy\Mcm\Model\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Retrieve status options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Active')],
            ['value' => 0, 'label' => __('Inactive')]

        ];
    }
}