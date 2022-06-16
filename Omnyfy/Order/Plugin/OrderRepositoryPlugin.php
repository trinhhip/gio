<?php
namespace Omnyfy\Order\Plugin;

use Magento\Framework\Exception\AuthorizationException;

class OrderRepositoryPlugin
{
    /**
     * @var \Magento\Framework\Api\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupFactory
     */
    protected $filterGroupFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Omnyfy\VendorAuth\Helper\VendorApi
     */
    protected $vendorApiHelper;

    /**
     * @var \Omnyfy\Order\Helper\Order
     */
    protected $orderHelper;

    protected $productRepository;

    protected $vendorResource;

    protected $queueHelper;

    /**
     * OrderRepositoryPlugin constructor
     * @param \Magento\Framework\Api\Filter $filter
     * @param \Magento\Framework\Api\Search\FilterGroupFactory $filterGroupFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
     * @param \Omnyfy\Order\Helper\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\Api\Filter $filter,
        \Magento\Framework\Api\Search\FilterGroupFactory $filterGroupFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResourceConnection $resource,
        \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper,
        \Omnyfy\Order\Helper\Order $orderHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Omnyfy\Core\Helper\Queue $queueHelper
    ){
        $this->filter = $filter;
        $this->filterGroupFactory = $filterGroupFactory;
        $this->request = $request;
        $this->connection = $resource->getConnection();
        $this->vendorApiHelper = $vendorApiHelper;
        $this->orderHelper = $orderHelper;
        $this->productRepository = $productRepository;
        $this->vendorResource = $vendorResource;
        $this->queueHelper = $queueHelper;
    }

    public function beforeGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
                                                    $id
    ){
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken > 0) {
            $orderIds = $this->getOrderIdsByVendorId($vendorIdFromToken);

            if (array_search($id, $orderIds) === false) {
                throw new AuthorizationException(__('Consumer is not authorized to access %resources'));
            }
        }
    }

    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
                                                    $result,
                                                    $id
    ){
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken === 0) { //integration token
            $param = $this->request->getParams();
            if (isset($param['vendor_id'])) {
                $vendorIdFromToken = $param['vendor_id'];
            }
        }

        if ($vendorIdFromToken > 0) {
            $orderId = $result->getEntityId();
            $result = $this->setVendorOrder($orderId, $vendorIdFromToken, $result);

            $orderItems = $result->getItems();
            $arrItems = [];

            foreach ($orderItems as $item) {
                if ($item->getVendorId() == $vendorIdFromToken) {
                    $arrItems[] = $item;
                }
            }
            $result->setItems($arrItems);

            $shippingAssgn = $result->getExtensionAttributes()->getShippingAssignments();

            foreach($shippingAssgn as $assignments){
                $arrShipItems = [];
                $shipItems = $assignments->getItems();

                foreach ($shipItems as $shipItem) {
                    if ($shipItem->getVendorId() == $vendorIdFromToken) {
                        $arrShipItems[] = $shipItem;
                    }
                }
                $assignments->setItems($arrShipItems);
            }
        }

        return $result;
    }

    public function beforeGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
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
            $orderIds = $this->getOrderIdsByVendorId($vendorIdFromToken);

            $filterByVendor = $this->filter->setField("entity_id")
                ->setValue($orderIds)
                ->setConditionType("in");

            $filterGroupByVendor = $this->filterGroupFactory->create();
            $filterGroupByVendor->setFilters([$filterByVendor]);

            $arrFilterGroup[] = $filterGroupByVendor;
            if($searchCriteria->getFilterGroups()){
                foreach ($searchCriteria->getFilterGroups() as $searchFilterGroup){
                    $filterFromParam = [];
                    foreach ($searchFilterGroup->getFilters() as $filter){
                        $filterFromParam[] = $filter;
                    }
                    $filterGroupFromParam = $this->filterGroupFactory->create();
                    $filterGroupFromParam->setFilters($filterFromParam);
                    $arrFilterGroup[] = $filterGroupFromParam;
                }
            }

            $searchCriteria->setFilterGroups($arrFilterGroup);
        }

        return [$searchCriteria];
    }

    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
                                                    $result,
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
            $orders = $result->getItems();

            foreach ($orders as $order) {
                $orderId = $order->getEntityId();

                $vendorItemsTotals = $this->orderHelper->getVendorItemsTotals($order, $vendorIdFromToken);
                $order = $this->setVendorItemsTotals($order, $vendorItemsTotals);

                $shippingFeeDetail = $this->orderHelper->getShippingFeesPerVendor($order, $vendorIdFromToken);
                $order = $this->setShippingFeeDetail($order, $shippingFeeDetail);

                $orderItems = $order->getItems();
                $arrItems = [];

                foreach ($orderItems as $item) {
                    if ($item->getVendorId() == $vendorIdFromToken) {
                        $arrItems[] = $item;
                    }
                }
                $order->setItems($arrItems);

                $shippingAssgn = $order->getExtensionAttributes()->getShippingAssignments();
                foreach($shippingAssgn as $assignments){
                    $shipping = $assignments->getShipping();
                    $vendorShippingMethod = $this->getVendorShippingMethod($vendorIdFromToken, $shipping->getMethod());
                    $shipping->setMethod($vendorShippingMethod);
                    $total = $shipping->getTotal();
                    $total = $this->setShippingTotalDetail($total, $shippingFeeDetail);

                    $arrShipItems = [];
                    $shipItems = $assignments->getItems();
                    foreach ($shipItems as $shipItem) {
                        if ($shipItem->getVendorId() == $vendorIdFromToken) {
                            $arrShipItems[] = $shipItem;
                        }
                    }
                    $assignments->setItems($arrShipItems);
                }
            }
        }

        return $result;
    }

    public function beforeSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $entity
    ) {
        //Implement logic only at order creation
        if ($entity->getEntityId()) {
            return [$entity];
        }
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        $sourceRequireShipping = [];
        if ($vendorIdFromToken === 0) { //integration token
            $param = $this->request->getParams();
            if (isset($param['vendor_id'])) {
                $vendorIdFromToken = $param['vendor_id'];
            } else {
                foreach ($entity->getItems() as $item) {
                    $this->setItemData($sourceRequireShipping, $item);
                }
            }
        }
        if (!empty($vendorIdFromToken)) {
            foreach ($entity->getItems() as $item) {
                $this->setItemData($sourceRequireShipping, $item, $vendorIdFromToken);
            }
        }

        //Don't have to set shipping method if cart is virtual or downloadable
        if (empty($sourceRequireShipping)) {
            return [$entity];
        }

        if (!$this->validateShippingMethod($entity, $sourceRequireShipping)) {
            throw new \Exception(__("Invalid shipping method"));
        }

        return [$entity];
    }

    /**
     * Process queues for Mcm
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @param \Magento\Sales\Api\Data\OrderInterface $result
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $entity,
        \Magento\Sales\Api\Data\OrderInterface $result
    ){
        //Implement logic only at order creation
        if ($entity->getEntityId()) {
            return $result;
        }
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken === 0) { //integration token
            $param = $this->request->getParams();
            if (isset($param['vendor_id'])) {
                $vendorIdFromToken = $param['vendor_id'];
            } else {
                foreach ($entity->getItems() as $item) {
                    $this->setItemData($item);
                }
            }
        }
        if (!empty($vendorIdFromToken)) {
            $orderId = $result->getId();
            $orderVendorRelation = [['order_id' => $orderId, 'vendor_id' => $vendorIdFromToken]];
            $this->vendorResource->saveOrderRelation($orderVendorRelation);
            $this->queueHelper->sendMsgToQueue('vendor_order_total', json_encode(['order_id' => $orderId]));
            $this->queueHelper->sendMsgToQueue('mcm_after_place_order', json_encode(['order_id' => $orderId]));
        }

        return $result;
    }

    private function validateShippingMethod($entity, $sourceRequireShipping)
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            return false;
        }
        $shippingAssignments = $extensionAttributes->getShippingAssignments();
        if (empty($shippingAssignments[0])) {
            return false;
        }
        $shippingExtensionAttributes = $shippingAssignments[0]->getShipping()->getExtensionAttributes();
        if (empty($shippingExtensionAttributes)) {
            return false;
        }
        $methods = $shippingExtensionAttributes->getMethods();
        if (empty($methods) || !is_array($methods)) {
            return false;
        }
        $shippingMethod = [];
        foreach ($methods as $method) {
            $shippingMethod[$method->getSourceStockId()] = $method->getMethodCode();
        }
        foreach ($sourceRequireShipping as $sourceStockId) {
            if (!in_array($sourceStockId, array_keys($shippingMethod))) {
                return false;
            }
        }
        $shippingAssignments[0]->getShipping()->setMethod(json_encode($shippingMethod));
        return true;
    }

    private function setItemData(&$sourceRequireShipping, $item, $vendorId = null)
    {
        $product = $this->productRepository->get($item->getSku());
        if (empty($vendorId)) {
            $vendorId = $this->vendorResource->getVendorIdByProductId($product->getId());
            if (!$vendorId) {
                throw new \Exception(__("Product %1 hasn't been assign to any vendors", $product->getSku()));
            }
        }
        if (empty($item->getExtensionAttributes()) || empty($item->getExtensionAttributes()->getSourceStockId())) {
            throw new \Exception(__("entity.items.extension_attributes.source_stock_id is a required field"));
        }
        $sourceStockId = $item->getExtensionAttributes()->getSourceStockId();
        if (!empty($vendorId)) {
            $item->setProductId($product->getId());
            $item->setProductType($product->getTypeId());
            $item->setWeight($product->getWeight());
            $item->setRowWeight($item->getQtyOrdered() * $product->getWeight());
            $item->setPrice($product->getFinalPrice());
            $item->setOriginalPrice($product->getPrice());
            $item->setName($product->getName());
            $item->setIsVirtual($product->getTypeId() == 'virtual');
            $item->setVendorId($vendorId);
            $qty = $this->orderHelper->getProductQty($vendorId, $product->getSku(), $sourceStockId);
            if ($qty === false) {
                throw new \Exception(__("Source stock is not correct for product %1", $product->getSku()));
            }
            if ($qty < $item->getQtyOrdered()) {
                throw new \Exception(__("We don't have enough product %1 as you requested", $product->getSku()));
            }
            $item->setSourceStockId($sourceStockId);
            if ($item->getProductType() != 'virtual' && $item->getProductType() != 'downloadable') {
                $sourceRequireShipping[] = $sourceStockId;
            }
        }
    }

    private function getOrderIdsByVendorId($vendorId){
        $select = $this->connection->select()
            ->from($this->connection->getTableName('omnyfy_vendor_vendor_order'), 'order_id')
            ->where('vendor_id = ?', $vendorId);
        $orderIds = $this->connection->fetchCol($select);

        return $orderIds;
    }

    private function setVendorOrder($orderId, $vendorId, $order){
        $select = $this->connection->select()
            ->from($this->connection->getTableName('omnyfy_mcm_vendor_order'))
            ->where('order_id = ?', $orderId)
            ->where('vendor_id = ?', $vendorId);

        $vendorOrder = $this->connection->fetchRow($select);

        if ($vendorOrder['id']) {
            $order->setBaseDiscountAmount($vendorOrder['base_discount_amount']);
            $order->setBaseGrandTotal($vendorOrder['base_grand_total']);
            $order->setBaseShippingAmount($vendorOrder['base_shipping_amount']);
            $order->setBaseShippingInclTax($vendorOrder['base_shipping_incl_tax']);
            $order->setBaseShippingTaxAmount($vendorOrder['base_shipping_tax']);
            $order->setBaseSubtotal($vendorOrder['base_subtotal']);
            $order->setBaseSubtotalInclTax($vendorOrder['base_subtotal_incl_tax']);
            $order->setBaseTaxAmount($vendorOrder['base_tax_amount']);
            $order->setDiscountAmount($vendorOrder['discount_amount']);
            $order->setGrandTotal($vendorOrder['grand_total']);
            $order->setShippingAmount($vendorOrder['shipping_amount']);
            $order->setShippingDiscountAmount($vendorOrder['shipping_discount_amount']);
            $order->setShippingInclTax($vendorOrder['shipping_incl_tax']);
            $order->setShippingTaxAmount($vendorOrder['shipping_tax']);
            $order->setSubtotal($vendorOrder['subtotal']);
            $order->setSubtotalInclTax($vendorOrder['subtotal_incl_tax']);
            $order->setTaxAmount($vendorOrder['tax_amount']);
        }

        return $order;
    }

    private function setVendorItemsTotals($order, $vendorItemsTotals)
    {
        if (isset($vendorItemsTotals)) {
            $order->setBaseDiscountAmount($vendorItemsTotals['base_discount_amount']);
            $order->setBaseGrandTotal($vendorItemsTotals['base_grand_total']);
            $order->setBaseSubtotal($vendorItemsTotals['base_subtotal']);
            $order->setBaseSubtotalInclTax($vendorItemsTotals['base_subtotal_incl_tax']);
            $order->setBaseTaxAmount($vendorItemsTotals['base_tax_amount']);
            $order->setDiscountAmount($vendorItemsTotals['discount_amount']);
            $order->setGrandTotal($vendorItemsTotals['grand_total']);
            $order->setSubtotal($vendorItemsTotals['subtotal']);
            $order->setSubtotalInclTax($vendorItemsTotals['subtotal_incl_tax']);
            $order->setTaxAmount($vendorItemsTotals['tax_amount']);
        }
        return $order;
    }

    private function setShippingFeeDetail($order, $shippingFeeDetail)
    {
        if (isset($shippingFeeDetail)) {
            $order->setBaseShippingAmount($shippingFeeDetail['base_shipping_amount']);
            $order->setBaseShippingInclTax($shippingFeeDetail['base_shipping_incl_tax']);
            $order->setBaseShippingTaxAmount($shippingFeeDetail['base_shipping_tax']);
            $order->setBaseShippingInvoiced($shippingFeeDetail['base_shipping_amount']);
            $order->setShippingAmount($shippingFeeDetail['shipping_amount']);
            $order->setShippingDiscountAmount($shippingFeeDetail['shipping_discount_amount']);
            $order->setShippingInclTax($shippingFeeDetail['shipping_incl_tax']);
            $order->setShippingTaxAmount($shippingFeeDetail['shipping_tax']);
            $order->setShippingInvoiced($shippingFeeDetail['shipping_amount']);
            $order->setShippingDescription($shippingFeeDetail['shipping_description']);
        }
        return $order;
    }

    private function setShippingTotalDetail($total, $shippingFeeDetail)
    {
        //Setting value on extension_attributes.shipping_assignments.shipping.total
        if (isset($shippingFeeDetail)) {
            $total->setBaseShippingAmount($shippingFeeDetail['base_shipping_amount']);
            $total->setBaseShippingInclTax($shippingFeeDetail['base_shipping_incl_tax']);
            $total->setBaseShippingTaxAmount($shippingFeeDetail['base_shipping_tax']);
            $total->setBaseShippingInvoiced($shippingFeeDetail['base_shipping_amount']);
            $total->setShippingAmount($shippingFeeDetail['shipping_amount']);
            $total->setShippingDiscountAmount($shippingFeeDetail['shipping_discount_amount']);
            $total->setShippingInclTax($shippingFeeDetail['shipping_incl_tax']);
            $total->setShippingTaxAmount($shippingFeeDetail['shipping_tax']);
            $total->setShippingInvoiced($shippingFeeDetail['shipping_amount']);
        }
        return $total;
    }

    private function getVendorShippingMethod($vendorId, $method)
    {
        if ($method) {
            $vendorShippingMethod = [];
            $arrMethod = json_decode($method, true);
            $sourceStockIds = $this->orderHelper->getSourceStockIdsByVendorId($vendorId);

            foreach ($sourceStockIds as $sourceStockId) {
                if (!empty($arrMethod)) {
                    if(array_key_exists($sourceStockId, $arrMethod)){
                        $vendorShippingMethod[$sourceStockId] = $arrMethod[$sourceStockId];
                    }
                }

            }
            return json_encode($vendorShippingMethod);
        }else{
            return null;
        }
    }
}
