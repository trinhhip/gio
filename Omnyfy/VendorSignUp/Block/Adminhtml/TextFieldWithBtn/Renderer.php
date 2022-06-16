<?php

namespace Omnyfy\VendorSignUp\Block\Adminhtml\TextFieldWithBtn;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Escaper;

class Renderer extends Text
{

    private $getGeoCodeBlock;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        GetGeoCode $getGeoCodeBlock,
        $data = []
    ) {
        $this->getGeoCodeBlock = $getGeoCodeBlock;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    public function getElementHtml()
    {
        $this->addClass('gg-vendor-address-field');
        $html = parent::getElementHtml();
        $html .= $this->getButtonHtml();
        return $html;
    }

    private function getButtonHtml()
    {
       return $this->getGeoCodeBlock->toHtml();
    }

}