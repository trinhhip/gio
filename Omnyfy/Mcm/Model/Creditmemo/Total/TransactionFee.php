<?php

namespace Omnyfy\Mcm\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

/**
 * Class Fee
 * @package Omnyfy\Mcm\Model\Invoice\Total
 */
class TransactionFee extends AbstractTotal
{
    /*
     * @var \Omnyfy\Mcm\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Omnyfy\Mcm\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Omnyfy\Mcm\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($data);
    }

    /**
     * @param Creditmemo $creditmemo
     * @return $this|TransactionFee
     */
    public function collect(Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $items = $order->getItemsCollection();
        $itemsLeft = 0;
        $itemsRefunding = 0;
        $lastCreditmemo = false;
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            if ($item->canRefund()) {
                $itemsLeft += $item->getQtyToRefund();
            }
        }
        foreach ($creditmemo->getItems() as $item) {
            if ($item->getOrderItem()->getParentItem()) {
                continue;
            }
            $itemsRefunding += $item->getQty();
        }
        if ($itemsRefunding == $itemsLeft) {
            $lastCreditmemo = true;
        }

        if(!$lastCreditmemo) {
            return $this;
        }
        $mcmInclTax = $creditmemo->getOrder()->getMcmTransactionFeeInclTax();
        $baseMcmInclTax = $creditmemo->getOrder()->getMcmBaseTransactionFeeInclTax();

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $mcmInclTax);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseMcmInclTax);

        return $this;
    }
}
