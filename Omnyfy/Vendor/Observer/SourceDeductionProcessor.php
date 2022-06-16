<?php

namespace Omnyfy\Vendor\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionServiceInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryShipping\Model\GetItemsToDeductFromShipment;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventoryShipping\Model\SourceDeductionRequestFromShipmentFactory;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Omnyfy\Vendor\Model\Resource\Vendor;
use Omnyfy\Vendor\Model\Order\DeductProcessor;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Omnyfy\Vendor\Helper\Data;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

class SourceDeductionProcessor extends \Magento\InventoryShipping\Observer\SourceDeductionProcessor
{
    private $isSingleSourceMode;
    private $defaultSourceProvider;
    private $getItemsToDeductFromShipment;
    private $sourceDeductionRequestFromShipmentFactory;
    private $sourceDeductionService;
    private $itemsToSellFactory;
    private $placeReservationsForSalesEvent;
    private $vendorResource;
    private $deductProcessor;
    private $websiteRepository;
    private $dataHelper;
    private $getStockItemConfiguration;

    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetItemsToDeductFromShipment $getItemsToDeductFromShipment,
        SourceDeductionRequestFromShipmentFactory $sourceDeductionRequestFromShipmentFactory,
        SourceDeductionServiceInterface $sourceDeductionService,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        Vendor $vendorResource,
        DeductProcessor $deductProcessor,
        WebsiteRepositoryInterface $websiteRepository,
        Data $dataHelper,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        parent::__construct($isSingleSourceMode, $defaultSourceProvider, $getItemsToDeductFromShipment, $sourceDeductionRequestFromShipmentFactory, $sourceDeductionService, $itemsToSellFactory, $placeReservationsForSalesEvent);
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getItemsToDeductFromShipment = $getItemsToDeductFromShipment;
        $this->sourceDeductionRequestFromShipmentFactory = $sourceDeductionRequestFromShipmentFactory;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->vendorResource = $vendorResource;
        $this->deductProcessor = $deductProcessor;
        $this->websiteRepository = $websiteRepository;
        $this->dataHelper = $dataHelper;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        if ($shipment->getOrigData('entity_id')) {
            return;
        }

        if (!empty($shipment->getExtensionAttributes())
            && !empty($shipment->getExtensionAttributes()->getSourceCode())) {
            $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
        } elseif ($this->isSingleSourceMode->execute()) {
            $sourceCode = $this->defaultSourceProvider->getCode();
        }
        $orderId = $shipment->getOrder()->getId();
        $stockId = $this->getStockId($shipment);
        $shipmentItems = $this->getItemsToDeductFromShipment->execute($shipment);

        /**
         * Execute deduct qty per item
         * If the quantity of the Product has been deducted when Invoicing, It will not be deducted again
         */
        foreach ($shipmentItems as $item) {
            $dataDeduct[] = [
                'sku' => $item->getSku(),
                'quantity' => $item->getQty(),
                'source_code' => $sourceCode,
                'order_id' => $orderId,
                'stock_id' => $stockId
            ];
            $stockItemConfiguration = $this->getStockItemConfiguration->execute(
                $item->getSku(),
                $stockId
            );

            if (!$stockItemConfiguration->isManageStock()) {
                //We don't need to Manage Stock
                continue;
            }

            if (!$this->deductProcessor->isDeducted($dataDeduct[0])) {
                $this->deductProcessor->execute($dataDeduct);
                $arr[] = $item;
                $sourceDeductionRequest = $this->sourceDeductionRequestFromShipmentFactory->execute(
                    $shipment,
                    $sourceCode,
                    $arr
                );
                $this->sourceDeductionService->execute($sourceDeductionRequest);
            } else {
                // deduct stock QTY in table inventory_stock_[stock_id]
                $this->deductProcessor->deductStockQty($dataDeduct[0]);
            }
        }
        /**
         * Always save shipment revertasion to table inventory_reservation
         * For case QTY was deducted when invoice, it will be added to table omnyfy_inventory_reservation
         * It will not be passed when check product is deducted or not
         */
        $sourceDeductionRequest = $this->sourceDeductionRequestFromShipmentFactory->execute(
            $shipment,
            $sourceCode,
            $shipmentItems
        );
        $this->placeCompensatingReservation($sourceDeductionRequest);
    }

    private function placeCompensatingReservation(SourceDeductionRequestInterface $sourceDeductionRequest): void
    {
        $items = [];
        foreach ($sourceDeductionRequest->getItems() as $item) {
            $items[] = $this->itemsToSellFactory->create([
                'sku' => $item->getSku(),
                'qty' => $item->getQty()
            ]);
        }
        $this->placeReservationsForSalesEvent->execute(
            $items,
            $sourceDeductionRequest->getSalesChannel(),
            $sourceDeductionRequest->getSalesEvent()
        );
    }

    public function getStockId($shipment) {
        $websiteId = $shipment->getOrder()->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        return $this->dataHelper->getStockIdByWebsiteCode($websiteCode);
    }
}
