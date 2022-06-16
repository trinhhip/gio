<?php
namespace Omnyfy\Vendor\Plugin\Creditmemo;

class SubtotalPlugin
{
    public function aroundCollect(
        \Magento\Sales\Model\Order\Creditmemo\Total\Subtotal $subject,
        callable $proceed,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ) {
        $countQtyRefund = 0;
        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getQty() == 0) {
                $countQtyRefund++;
            }
        }

        if (count($creditmemo->getAllItems()) == $countQtyRefund) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The refund can\'t be created without products. Add products and try again.')
            );
        } else {
            $proceed($creditmemo);
        }
    }
}
