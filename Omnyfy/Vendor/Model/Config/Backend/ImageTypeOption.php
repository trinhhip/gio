<?php
namespace Omnyfy\Vendor\Model\Config\Backend;

use Magento\Framework\Data\OptionSourceInterface;

class ImageTypeOption implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'jpg',
                'label' => 'JPG'
            ],
            [
                'value' => 'jpeg',
                'label' => 'JPEG'
            ],
            [
                'value' => 'gif',
                'label' => 'GIF'
            ],
            [
                'value' => 'png',
                'label' => 'PNG'
            ],
            [
                'value' => 'svg',
                'label' => 'SVG'
            ]
        ];
    }
}