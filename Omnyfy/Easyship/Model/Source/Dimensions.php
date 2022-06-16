<?php
namespace Omnyfy\Easyship\Model\Source;

class Dimensions implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(){
        $format = [
            ['value' => '4x6','label' => '4x6'],
            ['value' => 'A4','label' => 'A4']
        ];
        return $format;
    }
}
