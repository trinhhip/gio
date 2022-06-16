<?php
namespace Omnyfy\Order\Plugin;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\Shipment\OrderRegistrarInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use \Magento\Framework\Exception\CouldNotSaveException;

class ShipmentRepositoryPlugin
{
    /**
     * @var \Magento\Framework\Api\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Omnyfy\Order\Helper\ShipmentApi
     */
    protected $shipmentApiHelper;

    /**
     * @var \Omnyfy\VendorAuth\Helper\VendorApi
     */
    protected $vendorApiHelper;

    protected $resourceConnection;

    protected $orderStateResolver;

    protected $config;

    protected $orderItemRepository;

    protected $orderRepository;

    /**
     * OrderRepositoryPlugin constructor
     * @param \Magento\Framework\Api\Filter $filter
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Omnyfy\Order\Helper\ShipmentApi $shipmentApiHelper
     * @param \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
     */
    public function __construct(
        \Magento\Framework\Api\Filter $filter,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResourceConnection $resource,
        \Omnyfy\Order\Helper\ShipmentApi $shipmentApiHelper,
        \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper,
        OrderStateResolverInterface $orderStateResolver,
        OrderConfig $config,
        OrderItemRepositoryInterface $orderItemRepository,
        OrderRepositoryInterface $orderRepository
    ){
        $this->filter = $filter;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->request = $request;
        $this->connection = $resource->getConnection();
        $this->shipmentApiHelper = $shipmentApiHelper;
        $this->vendorApiHelper = $vendorApiHelper;
        $this->resourceConnection = $resource;
        $this->orderStateResolver = $orderStateResolver;
        $this->config = $config;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
    }

    public function beforeGet(
        \Magento\Sales\Api\ShipmentRepositoryInterface $subject,
        $id
    ){
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken > 0) {
            $orderIds = $this->getShipmentIdsByVendorId($vendorIdFromToken);

            if (array_search($id, $orderIds) === false) {
                throw new AuthorizationException(__('Consumer is not authorized to access %resources'));
            }
        }
    }

    public function beforeGetList(
        \Magento\Sales\Api\ShipmentRepositoryInterface $subject,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ){
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken === 0) { //integration token
            $param = $this->request->getParams();
            if (isset($param['vendor_id'])) {
                $vendorIdFromToken = $param['vendor_id'];
            }
        }

        if ($vendorIdFromToken > 0) {
            $shipmentIds = $this->getShipmentIdsByVendorId($vendorIdFromToken);

            $filters[] = $this->filter->setField("entity_id")
                ->setValue($shipmentIds)
                ->setConditionType("in");

            if($searchCriteria->getFilterGroups()){
                foreach ($searchCriteria->getFilterGroups() as $key => $filterGroup){
                    $filters[] = $filterGroup->getFilters()[$key];
                }
            }

            $filterGroup = [];
            if(count($filters) > 0){
                foreach ($filters as $data){
                    $filterGroup[] = $this->filterGroupBuilder->addFilter($data)->create();
                }
            }
            $searchCriteria->setFilterGroups($filterGroup);
        }

        return [$searchCriteria];
    }

    public function beforeSave(
        \Magento\Sales\Api\ShipmentRepositoryInterface $subject,
        \Magento\Sales\Api\Data\ShipmentInterface $entity
    ) {
        //Validate entity_id from payload
        if ($entity->getEntityId()) {
            $subject->get($entity->getEntityId());
        }

        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken > 0 || $vendorIdFromToken === 0) {
            $totalQty = 0;
            $shipmentItems = $entity->getItems();

            //Validate multiple vendor item
            $this->shipmentApiHelper->validateMultipleVendorItems($shipmentItems, $vendorIdFromToken);

            /** @var \Magento\Sales\Model\Order\Shipment\Item $shipmentItem */
            foreach ($shipmentItems as $shipmentItem) {
                $orderItem = $this->orderItemRepository->get($shipmentItem->getOrderItemId());
                $this->validateItems($entity, $shipmentItem, $orderItem);

                if ($shipmentItem->getQty() > 0) {
                    if (!$shipmentItem->getOrderItem()->isDummy(true)) {
                        $totalQty += $shipmentItem->getQty();
                    }
                }

                if ($vendorIdFromToken === 0) {
                    //set vendor_id for shipment from order item
                    $vendorIdFromToken = $orderItem->getVendorId();
                }

                $sourceCode = $entity->getSourceCode();
                if(empty($entity->getSourceCode())){
                    $sourceCode = $entity->getExtensionAttributes()->getSourceCode();
                }

                if (!empty($sourceCode)) {
                    $sourceStockId = $this->getVendorSourceId($sourceCode, $vendorIdFromToken);

                    if (empty($sourceStockId)) {
                        throw new CouldNotSaveException(
                            __(__("Source Code %1 is not correct.", $sourceCode))
                        );
                    }
                    $entity->setSourceStockId($sourceStockId);
                    $entity->setSourceCode($sourceCode);
                    $entity->getExtensionAttributes()->setSourceCode($sourceCode);

                } else if (empty($entity->getSourceStockId())) {
                    $entity->setSourceStockId($orderItem->getSourceStockId());
                    $sourceCode = $this->getVendorSourceCode($orderItem->getSourceStockId(), $vendorIdFromToken);
                    $entity->setSourceCode($sourceCode);
                    $entity->getExtensionAttributes()->setSourceCode($sourceCode);
                }
            }
            if (empty($entity->getTotalQty())) {
                $entity->setTotalQty($totalQty);
            }

            $entity->setVendorId($vendorIdFromToken);
        }

        return [$entity];
    }

    public function afterSave(
        \Magento\Sales\Api\ShipmentRepositoryInterface $subject,
        \Magento\Sales\Api\Data\ShipmentInterface $result,
        \Magento\Sales\Api\Data\ShipmentInterface $entity
    ) {
        $order = $result->getOrder();
        if ($order->getState() === Order::STATE_NEW) {
            $order->setState(
                $this->orderStateResolver->getStateForOrder($order, [OrderStateResolverInterface::IN_PROGRESS])
            );
            $order->setStatus($this->config->getStateDefaultStatus($order->getState()));
        }
        $this->orderRepository->save($order);
        foreach ($entity->getItems() as $shipmentItem) {
            if ($shipmentItem->getQty() > 0) {
                $orderItem = $this->orderItemRepository->get($shipmentItem->getOrderItemId());

                //Increase Order Item shipped qty
                if ($orderItem->getQtyShipped() < $orderItem->getQtyOrdered()) {
                    $orderItem->setQtyShipped($orderItem->getQtyShipped() + $shipmentItem->getQty());
                    $this->orderItemRepository->save($orderItem);
                }
            }
        }

        return $result;
    }

    private function getVendorSourceId($sourceCode, $vendorId) {
        $select = $this->connection->select()
            ->from($this->connection->getTableName('omnyfy_vendor_source_stock'), 'id')
            ->where('vendor_id = ?', $vendorId)
            ->where('source_code like ?', $sourceCode);
        return $this->connection->fetchOne($select);
    }

    private function getVendorSourceCode($sourceStockId, $vendorId)
    {
        $select = $this->connection->select()
            ->from($this->connection->getTableName('omnyfy_vendor_source_stock'), 'source_code')
            ->where('vendor_id = ?', $vendorId)
            ->where('id = ?', $sourceStockId);
        return $this->connection->fetchOne($select);
    }

    private function getShipmentIdsByVendorId($vendorId){
        $select = $this->connection->select()
            ->from($this->connection->getTableName('sales_shipment'), 'entity_id')
            ->where('vendor_id = ?', $vendorId);
        $shipmentIds = $this->connection->fetchCol($select);

        return $shipmentIds;
    }

    private function validateItems($entity, $shipmentItem, $orderItem){
        if ($entity->getEntityId() > 0 && $shipmentItem->getParentId() != $entity->getEntityId()) {
            throw new CouldNotSaveException(
                __(__("Please enter the correct Parent ID for Shipment Item"))
            );
        }
        $orderItem = $this->orderItemRepository->get($shipmentItem->getOrderItemId());
        if ($orderItem->getOrderId() != $entity->getOrderId()) {
            throw new CouldNotSaveException(
                __(__("Please enter the correct Order item ID for this Order"))
            );
        }

        //Validate ship qty for updating shipment items
        if (!empty($shipmentItem->getId()) && $shipmentItem->getQty() > $orderItem->getQtyOrdered()) {
            throw new CouldNotSaveException(
                __(
                    'Maximum qty to ship for Order Item Id %1 is %2',
                    $orderItem->getItemId(),
                    $orderItem->getQtyOrdered()
                )
            );
        }

        //Validate ship qty if create new shipment items
        if (empty($shipmentItem->getId()) && $orderItem->getQtyOrdered() < ($orderItem->getQtyShipped() + $shipmentItem->getQty())) {
            throw new CouldNotSaveException(
                __(
                    'Maximum qty to ship for Order Item Id %1 is %2',
                    $orderItem->getItemId(),
                    $orderItem->getQtyOrdered() - $orderItem->getQtyShipped()
                )
            );
        }
    }
}
