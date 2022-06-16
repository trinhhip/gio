<?php
namespace Omnyfy\Order\Plugin;
use \Magento\Framework\Exception\CouldNotSaveException;

class ShipmentDocumentPlugin
{
    protected $connection;
    protected $orderItemRepository;
    protected $shipmentApiHelper;
    protected $vendorApiHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Omnyfy\Order\Helper\ShipmentApi $shipmentApiHelper,
        \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
    ){
        $this->connection = $resource->getConnection();
        $this->orderItemRepository = $orderItemRepository;
        $this->shipmentApiHelper = $shipmentApiHelper;
        $this->vendorApiHelper = $vendorApiHelper;
    }

    public function beforeCreate(
        \Magento\Sales\Model\Order\ShipmentDocumentFactory $subject,
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $items = [],
        array $tracks = [],
        \Magento\Sales\Api\Data\ShipmentCommentCreationInterface $comment = null,
        $appendComment = false,
        array $packages = [],
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface $arguments = null
    ){
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken > 0 || $vendorIdFromToken === 0) {
            //Validate multiple vendor item
            $this->shipmentApiHelper->validateMultipleVendorItems($items, $vendorIdFromToken);
        }

        return [$order, $items, $tracks, $comment, $appendComment, $packages, $arguments];
    }

    public function afterCreate(
        \Magento\Sales\Model\Order\ShipmentDocumentFactory $subject,
        \Magento\Sales\Api\Data\ShipmentInterface $result
    ){
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken > 0 || $vendorIdFromToken === 0) {
            $shipmentItems = $result->getItems();
            $sourceStockId = '';

            if (isset($shipmentItems)) {
                foreach ($shipmentItems as $shipmentItem) {
                    $orderItem = $this->orderItemRepository->get($shipmentItem->getOrderItemId());
                    $sourceStockId = $orderItem->getSourceStockId();
                }
                if ($vendorIdFromToken === 0) {
                    //set vendor_id for shipment from order item
                    $vendorIdFromToken = $orderItem->getVendorId();
                }
                $sourceCode = $this->getVendorSourceCode($sourceStockId, $vendorIdFromToken);

                $result->setVendorId($vendorIdFromToken);
                $result->setSourceCode($sourceCode);
                $result->setSourceStockId($sourceStockId);
            }
        }

        return $result;
    }

    private function getVendorSourceCode($sourceStockId, $vendorId)
    {
        $select = $this->connection->select()
            ->from($this->connection->getTableName('omnyfy_vendor_source_stock'), 'source_code')
            ->where('vendor_id = ?', $vendorId)
            ->where('id = ?', $sourceStockId);
        return $this->connection->fetchOne($select);
    }
}
