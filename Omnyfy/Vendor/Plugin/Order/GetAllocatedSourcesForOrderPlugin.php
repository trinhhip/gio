<?php

namespace Omnyfy\Vendor\Plugin\Order;

use Magento\InventoryShippingAdminUi\Model\ResourceModel\GetAllocatedSourcesForOrder;
use Magento\Framework\App\ResourceConnection;
use Magento\Backend\Model\Session;

class GetAllocatedSourcesForOrderPlugin
{
    private $resourceConnection;
    private $backendSession;

    public function __construct(
        ResourceConnection $resourceConnection,
        Session $session
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->backendSession = $session;
    }

    public function aroundExecute(GetAllocatedSourcesForOrder $subject, callable $proceed, int $orderId): array
    {
        if ($orderId == 383) {
            $b = 1;
        }
        $sources = [];
        $arrSourceNames = [];
        $arrSourceCode = [];
        $salesConnection = $this->resourceConnection->getConnection('sales');
        $shipmentTableName = $this->resourceConnection->getTableName('sales_shipment', 'sales');
        /** Get shipment ids for order */
        $shipmentSelect = $salesConnection->select()
            ->from(
                ['sales_shipment' => $shipmentTableName],
                ['shipment_id' => 'sales_shipment.entity_id']
            )
            ->where('sales_shipment.order_id = ?', $orderId);
        $shipmentsIds = $salesConnection->fetchCol($shipmentSelect);

        /** Get sources for shipment ids */
        $vendorInfo = $this->backendSession->getVendorInfo();
        if ($vendorInfo) {
            $vendorId = $vendorInfo['vendor_id'];
            if (!empty($shipmentsIds)) {
                $connection = $this->resourceConnection->getConnection();
                $inventorySourceTableName = $this->resourceConnection->getTableName('inventory_source');
                $inventoryShipmentSourceTableName = $this->resourceConnection->getTableName('inventory_shipment_source');
    
                $select = $connection->select()
                    ->from(
                        ['inventory_source' => $inventorySourceTableName],
                        ['source_name' => 'inventory_source.name']
                    )
                    ->joinInner(
                        ['shipment_source' => $inventoryShipmentSourceTableName],
                        'shipment_source.source_code = inventory_source.source_code',
                        []
                    )
                    ->group('inventory_source.source_code')
                    ->where('shipment_source.shipment_id in (?)', $shipmentsIds)
                    ->where('inventory_source.vendor_id = ?', $vendorId);
    
                $sources = $connection->fetchCol($select);
            }
        } else {
            if (!empty($shipmentsIds)) {
                $connection = $this->resourceConnection->getConnection();
                $inventorySourceTableName = $this->resourceConnection->getTableName('inventory_source');
                $inventoryShipmentSourceTableName = $this->resourceConnection->getTableName('inventory_shipment_source');
    
                $select = $connection->select()
                    ->from(
                        ['inventory_source' => $inventorySourceTableName],
                        ['source_name' => 'inventory_source.name']
                    )
                    ->joinInner(
                        ['shipment_source' => $inventoryShipmentSourceTableName],
                        'shipment_source.source_code = inventory_source.source_code',
                        []
                    )
                    ->group('inventory_source.source_code')
                    ->where('shipment_source.shipment_id in (?)', $shipmentsIds);
    
                $sources = $connection->fetchCol($select);
            }
        }

        return $sources;
    }
}
