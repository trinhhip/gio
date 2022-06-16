<?php
namespace Omnyfy\Easyship\Model\Source;

class LabelFileFormat implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(){
        $format = [
            ['value' => 'URL','label' => 'URL'],
            ['value' => 'PDF','label' => 'PDF'],
            ['value' => 'PNG','label' => 'PNG']
        ];
        return $format;
    }
}
