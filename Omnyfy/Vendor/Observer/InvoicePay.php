<?php
/**
 * Project: Multi Vendor M2.
 * User: jing
 * Date: 21/6/17
 * Time: 3:45 PM
 */

namespace Omnyfy\Vendor\Observer;

use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Vendor\Model\Resource\Vendor as VendorResource;

class InvoicePay implements ObserverInterface
{
    protected $vendorResource;

    protected $locationResource;

    protected $queueHelper;

    protected $websiteRepository;

    protected $dataHelper;

    protected $deductProcessor;

    protected $vendorSource;

    public function __construct(
        VendorResource $vendorResource,
        \Omnyfy\Vendor\Model\Resource\Location $locationResource,
        \Omnyfy\Core\Helper\Queue $queueHelper,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Omnyfy\Vendor\Helper\Data $dataHelper,
        \Omnyfy\Vendor\Model\Order\DeductProcessor $deductProcessor
    )
    {
        $this->vendorResource = $vendorResource;
        $this->locationResource = $locationResource;
        $this->queueHelper = $queueHelper;
        $this->websiteRepository = $websiteRepository;
        $this->dataHelper = $dataHelper;
        $this->deductProcessor = $deductProcessor;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getData('invoice');
        $invoiceId = $invoice->getId();
        if (empty($invoiceId)) {
            return;
        }

        //Only process for new invoice
        if ($invoice->getCreatedAt() !== $invoice->getUpdatedAt()) {
            return;
        }

        //get all vendor ids for this invoice and save into invoice-vendor relation
        $items = $invoice->getAllItems();
        $orderId = $invoice->getOrderId();
        $stockId = $this->getStockId($invoice);
        $vendorIds = [];
        $qtyToDeduct = [];
        // push data to array $qtyToDeduct
        foreach($items as $item) {
            $vendorId = $item->getVendorId();
            $sourceCode = $this->dataHelper->getSourceCodeById($item->getSourceStockId());
            if (!empty($vendorId)) {
                $vendorIds[] = $vendorId;
            }
            $qtyToDeduct[] = [
                'source_code' => $sourceCode,
                'quantity' => $item->getQty(),
                'sku' => $item->getSku(),
                'order_id' => $orderId,
                'stock_id' => $stockId,
            ];
        }
        $vendorIds = array_unique($vendorIds);

        if (empty($vendorIds)) {
            //TODO: throw exception or log errors
            return;
        }

        // $customerId = $invoice->getOrder()->getCustomerId();

        $data = [];
        foreach($vendorIds as $vendorId) {
            $data[] = ['invoice_id' => $invoiceId, 'vendor_id' => $vendorId];
        }

        $this->deductProcessor->execute($qtyToDeduct, true);
        $this->vendorResource->saveInvoiceRelation($data);

        // Comment this code because current do not have warehouse
        //Save customer favorite vendor if location is not warehouse
        // if (!empty($customerId) && count($locationIds) == 1) {
        //     $locationId = $locationIds[0];
        //     $warehouseIds = $this->locationResource->getWarehouseIds();

        //     if (!in_array($locationId, $warehouseIds)) {
        //         $vendorId = $vendorIds[0];
        //         $this->vendorResource->saveFavoriteVendorId($customerId, $vendorId);
        //     }
        // }

        //add invoice id to queue for vendor total in invoice calculation
        $this->queueHelper->sendMsgToQueue('vendor_invoice_total', json_encode(['invoice_id' => $invoiceId]));
    }

    public function getStockId($invoice) {
        $websiteId = $invoice->getOrder()->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        return $this->dataHelper->getStockIdByWebsiteCode($websiteCode);
    }
}
