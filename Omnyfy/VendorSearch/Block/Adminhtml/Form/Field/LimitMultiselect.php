<?php

namespace Omnyfy\VendorSearch\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form\Element\Multiselect;

class LimitMultiselect extends Multiselect {
    public function getElementHtml()
    {
        $this->addClass('limit-select');
        $html = parent::getElementHtml();
        $html .= $this->getLimitScript();
        return $html;
    }

    private function getLimitScript()
    {
        $script = '
        <script type="text/x-magento-init">
            {
               ".limit-select": {
                   "Omnyfy_VendorSearch/js/limit-select": {}
               }
            }
        </script>';
        return $script;
    }
}