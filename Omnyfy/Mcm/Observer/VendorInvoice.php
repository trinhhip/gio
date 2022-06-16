<?php

namespace Omnyfy\Mcm\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class AfterOrder
 * @package Omnyfy\Mcm\Observer
 */
class VendorInvoice implements ObserverInterface {
    /**
     * @var \Omnyfy\Mcm\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Omnyfy\Mcm\Model\ResourceModel\FeesManagement
     */
    protected $feesManagementResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    protected $_invoiceHelper;

    protected $queueHelper;

    /**
     * @param \Omnyfy\Mcm\Helper\Data 
     * @param \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Omnyfy\Mcm\Helper\Data $helper,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource,
        DateTime $date,
        \Omnyfy\Mcm\Helper\InvoiceHelper  $invoiceHelper,
        \Omnyfy\Core\Helper\Queue $queueHelper
    ) {
        $this->_helper = $helper;
        $this->feesManagementResource = $feesManagementResource;
        $this->_date = $date;
        $this->_invoiceHelper = $invoiceHelper;
        $this->queueHelper = $queueHelper;
    }

    /**
     * Set payment fee to order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $invoice = $observer->getData('invoice');
        $invoiceId = $invoice->getId();
        $orderId = $invoice->getOrderId();
        $eventName = $observer->getEvent()->getName();
        if (empty($invoiceId)) {
            return;
        }
        if($eventName == 'sales_order_invoice_save_after'){
        //Only process for new invoice
        if ($invoice->getCreatedAt() !== $invoice->getUpdatedAt()) {
            return;
            }
        }

        $items = $invoice->getAllItems();
        $vendorIds = [];
        foreach ($items as $item) {
            $vendorId = $item->getVendorId();
            if (!empty($vendorId)) {
                $vendorIds[] = $vendorId;
            }
        }
        $vendorIds = array_unique($vendorIds);

        if (empty($vendorIds)) {
            //TODO: throw exception or log errors
            return;
        }

        // Only run the MCM cron event if the mcm order has been processed
        // Otherwise wait for the order to be processed before running
        $hasMcmCronRun = $this->queueHelper->checkIfCronPending('mcm_after_place_order', array('order_id' => $invoice->getOrderId()));
        if ($hasMcmCronRun) {
            $this->_invoiceHelper->saveInvoiceData($vendorIds, $orderId, $invoiceId);
        }

        return $this;
    }

}
