<?php


namespace OmnyfyCustomzation\Mcm\Plugin\Order\View\Items;


use Omnyfy\Mcm\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer as OmnyfyDefaultRenderer;

class DefaultRenderer
{
    public function afterGetTemplate(OmnyfyDefaultRenderer $subject, $result){
        return 'OmnyfyCustomzation_Mcm::order/view/items/renderer/default.phtml';
    }
}