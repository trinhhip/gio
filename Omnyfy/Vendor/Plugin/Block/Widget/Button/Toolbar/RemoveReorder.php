<?php
namespace Omnyfy\Vendor\Plugin\Block\Widget\Button\Toolbar;

use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;

class RemoveReorder
{
    public function beforePushButtons(
        ToolbarContext $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\View) {
            return [$context, $buttonList];
        }

        $order = $context->getOrder();

        if (!empty($order)) {
            $buttonList->remove('order_reorder');
        }

        return [$context, $buttonList];
    }
}
