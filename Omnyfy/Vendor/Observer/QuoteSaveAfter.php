<?php
namespace Omnyfy\Vendor\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuoteSaveAfter  implements ObserverInterface
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($quote = $observer->getQuote()) {
            if($quote->getCustomerId() && $quote->getCustomerIsGuest()) {
                $quote->setCustomerIsGuest(0);
                $quote->save();
            }
        }
    }
}
