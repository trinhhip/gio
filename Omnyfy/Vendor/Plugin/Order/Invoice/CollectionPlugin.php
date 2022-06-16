<?php
namespace Omnyfy\Vendor\Plugin\Order\Invoice;

use Omnyfy\Vendor\Helper\Backend as BackendHelper;
use Magento\Framework\App\ResourceConnection;

class CollectionPlugin
{
    public function __construct(
        BackendHelper $backendHelper,
        ResourceConnection $resourceConnection
    ){
        $this->backendHelper = $backendHelper;
        $this->resourceConnection = $resourceConnection;
    }

    public function beforeLoadWithFilter(\Magento\Sales\Model\ResourceModel\Order\Invoice\Orders\Grid\Collection $subsject,
                                         $printQuery = false, $logQuery = false
    ) {
        $vendorId = $this->backendHelper->getBackendVendorId();
        if(!empty($vendorId)) {
            $adapter = $this->resourceConnection->getConnection();
            $sales_invoice_item = $this->resourceConnection->getTableName('sales_invoice_item');
            $sales_order_item = $this->resourceConnection->getTableName('sales_order_item');
            $parentIds = $adapter->select()->from(['svi' => $sales_invoice_item], ['parent_id'])
                ->joinLeft(['soi' => $sales_order_item],
                    'soi.item_id = svi.order_item_id',
                    ''
                )->where('soi.vendor_id = ?', $vendorId );
            $subsject->getSelect()->where('main_table.entity_id IN (?)', $parentIds);
        }
        return [$printQuery,$logQuery];
    }

}
