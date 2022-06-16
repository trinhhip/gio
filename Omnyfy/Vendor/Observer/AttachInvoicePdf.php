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
use Omnyfy\Vendor\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class AttachInvoicePdf implements ObserverInterface
{
    protected $printInvoice;

    public function __construct(PrintInvoice $printInvoice)
    {
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
            $transport = $observer->getData('transportObject');
            $data = $observer->getData('transport');

            $attachments = $transport->getData('attachments');
            $attachments = is_array($attachments) ? $attachments : array();

            $invoice = $data['invoice'];
            $vendors = [];
            foreach ($invoice->getAllItems() as $item) {
                $vendors[] = $item->getVendorId();
            }

            $vendors = array_unique($vendors);

            foreach ($vendors as $vendor) {

                $total = $this->printInvoice->totalGroup($invoice->getOrder(), $invoice, [$vendor]);

                if($total['subtotal'] > 0){
                    $pdf = $this->printInvoice->getInvoicePdf($data['invoice_id'], [$vendor]);
                    $attachments[] = array(
                        'content' => file_get_contents($pdf),
                        'type' => 'application/pdf',
                        'name' => basename($pdf),
                        'disposition' => \Zend_Mime::DISPOSITION_INLINE
                    );    
                }else{
                    throw new \Exception(__("Order Vendor's subtotal missing."));
                }         
            }

            if(count($attachments)){
                $transport->setData('attachments', $attachments);    
            }            
        }
    }
}
