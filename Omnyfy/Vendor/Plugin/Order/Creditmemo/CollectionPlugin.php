<?php
namespace Omnyfy\Vendor\Plugin\Order\Creditmemo;

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

    public function beforeLoadWithFilter(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Order\Grid\Collection $subsject,
    $printQuery = false, $logQuery = false
    ) {
        $vendorId = $this->backendHelper->getBackendVendorId();
        if(!empty($vendorId)) {
            $adapter = $this->resourceConnection->getConnection();
            $sales_creditmemo_item = $this->resourceConnection->getTableName('sales_creditmemo_item');
            $sales_order_item = $this->resourceConnection->getTableName('sales_order_item');
            $parentIds = $adapter->select()->from(['sci' => $sales_creditmemo_item], ['parent_id'])
                ->joinLeft(['soi' => $sales_order_item],
                    'soi.item_id = sci.order_item_id',
                   ''
                )->where('soi.vendor_id = ?', $vendorId );
            $subsject->getSelect()->where('main_table.entity_id IN (?)', $parentIds);
        }
        return [$printQuery,$logQuery];
    }

}
