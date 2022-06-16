<?php


namespace Omnyfy\Mcm\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class McmVendorOrderHelper extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * McmVendorOrderItem constructor.
     * @param Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        parent::__construct($context);
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    public function getVendorOrders($orderItems): array
    {
        $orderVendor = [];
        foreach ($orderItems as $vendorId => $orderItem){
            $select = $this->connection->select()->from($this->connection->getTableName('omnyfy_mcm_vendor_order'), 'id')
                ->where('vendor_id= ?', $vendorId)
                ->where('order_id=?', $orderItem->getOrderId());
            $orderVendorId = $this->connection->fetchOne($select);
            if($orderVendorId) {
                $orderVendor[$vendorId] = $orderVendorId;
            }
        }
        return $orderVendor;
    }

}
