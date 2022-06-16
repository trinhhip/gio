<?php


namespace OmnyfyCustomzation\EmailTemplate\Observer;


class InvoiceOrder implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getData('transportObject');
        $order = $transport->getOrder();
        $invoice = $transport->getInvoice();
        if ($order && $invoice) {
            $titleInvoice = $order->getIncrementId() == $invoice->getIncrementId()
                ? __('Your Invoice for Order #%1', $order->getIncrementId())
                : __('Your Invoice #%1 for Order #%2', $invoice->getIncrementId(), $order->getIncrementId());
            $transport->setData('titleInvoice', $titleInvoice);
        }
    }
}