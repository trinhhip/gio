<?php
namespace Omnyfy\Easyship\Model\Source;

class TaxPayer implements \Magento\Framework\Data\OptionSourceInterface
{  
    public function toOptionArray(){
        $format = [
            ['value' => 'Sender','label' => 'Sender'],
            ['value' => 'Receiver','label' => 'Receiver']
        ];
        return $format;
    }
}
