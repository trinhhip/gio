<?php

namespace OmnyfyCustomzation\Customer\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class BusinessType
 *
 * @package OmnyfyCustomzation\Customer\Model\Config\Source
 */
class BusinessType extends AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        $options = [[
            'value' => '',
            'label' => __(' ')
        ]];

        foreach ($this->toArray() as $key => $label) {
            $options[] = [
                'value' => $key,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [
            'Art Gallery / Art Consultancy',
            'Co-Working / Co-Living Space',
            'Food & Beverage',
            'Hotel / Resort / Spa',
            'Interior Design / Architecture',
            'Real Estate Developer',
            'Retailer',
            'Sourcing Agent',
            'Other'
        ];
        $options = [];
        foreach ($data as $option) {
            $options[$option] = $option;
        }
        return $options;
    }
}