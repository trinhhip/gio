<?php

namespace OmnyfyCustomzation\OrderNote\Plugin\Order\View\Items;

use Omnyfy\Mcm\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer as OmnyfyDefaultRenderer;

class DefaultRenderer
{
    /**
     * @var \OmnyfyCustomzation\OrderNote\Helper\Data
     */
    private $helper;

    public function __construct(
        \OmnyfyCustomzation\OrderNote\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

    public function afterGetTemplate(OmnyfyDefaultRenderer $subject, $result){
        $isEnabled =  $this->helper->isEnabled();
        if($isEnabled) {
            return 'OmnyfyCustomzation_OrderNote::order/view/items/renderer/default.phtml';
        }

        return $result;
    }
}