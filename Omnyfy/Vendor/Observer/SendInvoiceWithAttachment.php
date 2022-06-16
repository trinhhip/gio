<?php
/**
 * Project: Multi Vendor M2.
 * User: seth
 * Date: 5/9/19
 * Time: 2:30 PM
 */

namespace Omnyfy\Vendor\Observer;

use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Mcm\Helper\PrintInvoice;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Omnyfy\Vendor\Model\Config;

class SendInvoiceWithAttachment implements ObserverInterface
{
    protected $invoiceSender;

    protected $printInvoice;

    public function __construct(
        InvoiceSender $invoiceSender,
        PrintInvoice $printInvoice
    )
    {
        $this->invoiceSender = $invoiceSender;
        $this->printInvoice = $printInvoice;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnabled = $this->printInvoice->getSystemConfig('omnyfy_vendor/vendor/invoice_attachment');
        $invoice_by = $this->printInvoice->getSystemConfig('omnyfy_vendor/vendor/invoice_by');

        if ($isEnabled && $invoice_by == Config::INVOICE_BY_VENDOR) {
            $order = $observer->getData('order');
            foreach ($order->getInvoiceCollection() as $invoice){
                $this->invoiceSender->send($invoice);
            }       
        }    
    }
}
