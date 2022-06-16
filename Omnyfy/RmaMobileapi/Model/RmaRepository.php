<?php
/**
 * Project: Rma Mobile API.
 * User: ab
 * Date: 2019-10-15
 * Time: 15:25
 */
namespace Omnyfy\RmaMobileapi\Model;


use Magento\Sales\Model\Order;
use Magento\Setup\Exception;

class RmaRepository implements \Omnyfy\RmaMobileapi\Api\RmaRepositoryInterface
{
    /**
     * @var \Magento\Sales\Model\OrderRepository Order
     */
    protected $_salesOrder;
    protected $_rmaFactory;
    protected $_itemFactory;
    protected $_reasonFactory;
    protected $_conditionsFactory;
    protected $_statusFactory;
    protected $_resolutionFactory;
    protected $_rmaSaveService;
    protected $_dataProcessor;
    protected $_customerStrategy;
    protected $_customerSession;
    protected $_addressFactory;

    /**
     * @var \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface
     */
    protected $_performFactory;

    protected $_statusFields = [
        'status_id',
        'name',
        'sort_order',
        'is_active',
        'code',
    ];

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $_customerInterface
     */
    protected $_customerInterface;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $_authSession;

    /**
     * @var \Omnyfy\Vendor\Model\Resource\Vendor
     */
    protected $_vendor;
    /**
     * @var \Mirasvit\Rma\Model\OfflineItemFactory
     */
    private $offlineItemFactory;


    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Sales\Model\OrderRepository $order,
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Mirasvit\Rma\Model\OfflineItemFactory $offlineItemFactory,
        \Mirasvit\Rma\Model\ReasonFactory $reasonFactory,
        \Mirasvit\Rma\Model\ConditionFactory $conditionsFactory,
        \Mirasvit\Rma\Model\StatusFactory $statusFactory,
        \Mirasvit\Rma\Model\ResolutionFactory $resolutionFactory,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SaveInterface $rmaSaveService,
        \Mirasvit\Rma\Controller\Rma\PostDataProcessor $dataProcessor,
        \Mirasvit\Rma\Helper\Controller\Rma\CustomerStrategy $customerStrategy,
        \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Mirasvit\Rma\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerInterface,
        \Magento\Customer\Model\Customer $customer,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendor
    )
    {
        $this->_authSession = $userContext;
        $this->_salesOrder = $order;
        $this->_rmaFactory = $rmaFactory;
        $this->_itemFactory = $itemFactory;
        $this->_reasonFactory = $reasonFactory;
        $this->_conditionsFactory = $conditionsFactory;
        $this->_statusFactory = $statusFactory;
        $this->_resolutionFactory = $resolutionFactory;
        $this->_performFactory = $performerFactory;
        $this->_rmaSaveService = $rmaSaveService;
        $this->_dataProcessor = $dataProcessor;
        $this->_customerStrategy = $customerStrategy;
        $this->_addressFactory = $addressFactory;
        $this->_customerInterface = $customerInterface;
        $this->_customer = $customer;
        $this->_vendor = $vendor;
        $this->offlineItemFactory = $offlineItemFactory;
    }

    /**
      * Returns RMA for order id
      *
      * @api
      * @param int $orderId Order ID.
      * @return \Omnyfy\Core\Api\Json
      */

    public function getByOrderId($orderId)
    {
        $order = $this->_salesOrder->get($orderId);
        $itemsId = array_keys($order->getItems());
        $rmaIds = $this->_itemFactory->create()->getCollection()->addFieldToFilter('order_item_id',['in' => $itemsId])->getColumnValues('rma_id');
        $rmaOfflineIds = $this->offlineItemFactory->create()->getCollection()->addFieldToFilter('offline_item_id',['in' => $itemsId])->getColumnValues('rma_id');
        $rmaIds = array_merge($rmaIds,$rmaOfflineIds);
        $collection = $this->_rmaFactory->create()->getCollection();
        $collection = $collection->addFieldToFilter('rma_id', ['in' => $rmaIds]);
        return $collection->toArray();
    }

    /**
      * Returns RMA item for rma id
      *
      * @api
      * @param int $rmaId RMA Id.
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaItemsForRmaId($rmaId)
    {
        /** @var  $collection */
        $collection = $this->_itemFactory->create()->getCollection();
        $collection = $collection->addFieldToFilter('rma_id', $rmaId);
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class)->debug($collection->getSelect());
        return $collection->toArray();
    }

    /**
      * Returns RMA items for rma ids
      *
      * @api
      * @param string $rmaIds RMA Ids.
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaItemsForRmaIds($rmaIds){
        $rmasIdArray = explode(',', $rmaIds);
        $collection = $this->_itemFactory->create()->getCollection();
        $collection = $collection->addFieldToFilter('rma_id', array('in', $rmasIdArray))->addFieldToFilter('qty_requested', array('gt' => 0));
        return $collection->toArray();
    }

    /**
      * Returns RMA reasons list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaReasonList(){
        $collection = $this->_reasonFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active', 1);
        return $collection->toArray();
    }

    /**
      * Returns RMA conditions list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaConditionList(){
        $collection = $this->_conditionsFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active', 1);
        return $collection->toArray();
    }

    /**
      * Returns RMA resolution list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaResolutionList(){
        $collection = $this->_resolutionFactory->create()->getCollection();
        $collection->addFieldToFilter('is_active', 1);
        return $collection->toArray();
    }

    /**
      * Returns RMA statuses list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaStatusList(){
        $collection =  $this->_statusFactory->create()->getCollection();
        $array = [];

        foreach ($collection as $value){
            $a = [];
            foreach ($this->_statusFields as $fieldName){
               $a[$fieldName] = $value[$fieldName];
            }
            $array[] = $a;
        }

        $arrayReturn = [];
        $arrayReturn['items'] = $array;
        $arrayReturn['totalRecords'] = count($array);
        return $arrayReturn;
    }

    /**
      * Returns RMA address list
      *
      * @api
      * @return \Omnyfy\Core\Api\Json
      */

    public function getRmaAddressList(){
        return $this->_addressFactory->create()->getCollection()->toArray();
    }

    /**
      * Save RMA request
      *
      * @api
      * @param \Omnyfy\RmaMobileapi\Api\Data\RmaOrderDataInterface $data.
      * @return \Omnyfy\Core\Api\Json
      */

    public function saveRma($data){
        $dataArray = $data->getData();
        $response = [];
        $response['result'] = false;

        if (!$this->_dataProcessor->validate($dataArray)) {
            $response['message'] = 'Failed validation.';
            return $response;
        }

        try {
            $order = $this->_salesOrder->get($dataArray['order_id']);
            $orderItems = $order->getItems();
            foreach($dataArray['items'] as $key=>$item){
                $orderItem = $orderItems[$key];
                if ($orderItem) {
                    $dataArray['items'][$key]["product_sku"] = $orderItem->getSku();
                    $dataArray['items'][$key]['order_id'] = $dataArray['order_id'];
                    $dataArray['items'][$key]['vendor_id'] = $orderItem->getData('vendor_id');
                }
            }
            $dataProcessed = $this->_dataProcessor->createOfflineOrder($dataArray);
            $dataProcessed['order_ids'] = [$dataArray['order_id']];
            $rma = $this->_rmaSaveService->saveRma(
                $this->_customerStrategy->getPerformer(),
                $this->_dataProcessor->filterRmaData($dataProcessed),
                $this->_dataProcessor->filterRmaItems($dataProcessed)
            );

            $response['result'] = true;
            return $response;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    /**
      * Returns Test string
      *
      * @api
      * @param int $orderId Order ID.
      * @return \Omnyfy\Core\Api\Json
      */

    public function test()
    {
        return "test: test";
    }


    /** @inheritdoc */
    public function getRmaItems($rmaIds){
        return $this->getRmaItemsForRmaIds($rmaIds);
    }

    /** @inheritdoc */
    public function VendorSaveRma($data){
        $dataArray = $data->getData();
        $response = [];
        $response['result'] = false;

        if (!$this->_dataProcessor->validate($dataArray)) {
            $response['message'] = 'Failed validation.';
            return $response;
        }

        try {
            /** $order \Magento\Sales\Api\Data\OrderInterface */
            if (!$order = $this->_salesOrder->get($dataArray['order_id']))
                throw new Exception("Orders not found for the order ID:".$dataArray['order_id']);


            if (!$order->getCustomerId())
                throw new Exception("RMA cannot be created for guest orders");


            /** @var int $userId */
            $userId = $this->_authSession->getUserId();


            if (!$userId)
                throw new \Exception(__("User is not authorized to update order"));

            if (empty($vendorId = $this->_vendor->getVendorIdByUserId($userId)))
                throw new \Exception(__("User is not authorized to update order : Not a valid vendor"));



//            $customer = $this->_customerInterface->getByid($order->getCustomerId());
            $customer = $this->_customer->load($order->getCustomerId());

            $performer = $this->_performFactory->create(
                \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface::CUSTOMER,
                $customer
                );
            /** default rma status set to 2 (Approved) for APP requests */
            $dataArray['status_id'] = 2;

            /** @var \Magento\Sales\Api\Data\OrderItemInterface[] $orderItems */
            $orderItems = $order->getItems();

            foreach($dataArray['items'] as $key=>$item){

                $orderItem = $orderItems[$key];


                $dataArray['items'][$key]["vendor_id"] = $vendorId;
				if ($orderItem)
					$dataArray['items'][$key]["product_sku"] = $orderItem->getSku();
                    $dataArray['items'][$key]['order_id'] = $dataArray['order_id'];
            }

            $dataProcessed = $this->_dataProcessor->createOfflineOrder($dataArray);
            $dataProcessed['order_ids'] = [$dataArray['order_id']];
            $rma = $this->_rmaSaveService->saveRma(
                $performer,
                $this->_dataProcessor->filterRmaData($dataProcessed),
                $this->_dataProcessor->filterRmaItems($dataProcessed)
            );

            $response['result'] = true;
            return $response;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }
}
