<?php

namespace Omnyfy\Mcm\Observer;

class TotalPayoutOnOrderCancel implements \Magento\Framework\Event\ObserverInterface
{
    protected $vendorOrderFactory;

    public function __construct(\Omnyfy\Mcm\Model\VendorOrderFactory $vendorOrderFactory)
    {
        $this->vendorOrderFactory = $vendorOrderFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        $vendorOrderMcm = $this->vendorOrderFactory->create()->load($orderId,'order_id');
        $vendorOrderMcm->setPayoutStatus(5); // Cancel Status
        $vendorOrderMcm->save();
    }
}
