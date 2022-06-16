<?php

namespace OmnyfyCustomzation\OrderNote\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use OmnyfyCustomzation\OrderNote\Helper\Data;

class SaveToOrderItem implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        if ($this->helper->isEnabled()) {
            $event = $observer->getEvent();
            $quote = $event->getQuote();
            $quoteItems = $quote->getItems();
            $noteItems = [];
            foreach ($quoteItems as $quoteItem) {
                $noteItems[$quoteItem->getData('item_id')] = $quoteItem->getData('order_note');
            }

            $order = $event->getOrder();
            $orderItems = $order->getItems();
            foreach ($orderItems as $orderItem) {
                $quoteItemId = $orderItem->getData('quote_item_id');
                $orderNote = isset($noteItems[$quoteItemId]) ? $noteItems[$quoteItemId] : '';
                $orderItem->setData('order_note', $orderNote);
            }
        }
    }
}
