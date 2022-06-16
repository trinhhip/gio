<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Order;

class AjaxSelectCourier extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $orderRepository;
    protected $courierFactory;
    protected $courierCollectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Easyship\Model\EasyshipSelectedCourierFactory $courierFactory,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipSelectedCourier\CollectionFactory $courierCollectionFactory
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->orderRepository = $orderRepository;
        $this->courierFactory = $courierFactory;
        $this->courierCollectionFactory = $courierCollectionFactory;
    }

    public function execute(){
        $return['message'] = null;
        $return['error'] = false;

        $data = $this->getRequest()->getParams();
        $locationId = $data['location_id'];
        $orderId = $data['order_id'];
        $order = $this->orderRepository->get($orderId);
        $quoteId = $order->getQuoteId();

        $courierData['courier_id'] = $data['courier_id'];
        $courierData['courier_name'] = $data['courier_name'];
        $courierData['total_charge'] = $data['total_charge'];

        $collection = $this->courierCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('vendor_location_id', $locationId);

        try {
            if ($collection->count() > 0) {
                foreach ($collection as $item) {
                    $courier = $this->courierFactory->create()->load($item->getEntityId());
                    $courier->setCourierId($data['courier_id']);
                    $courier->setCourierName($data['courier_name']);
                    $courier->setTotalCharge($data['total_charge']);
                    $courier->save();
                }
                $return['message'] = 'Success';
                $return['error'] = false;
                $this->messageManager->addSuccess(__('Selected courier has been updated.'));
            }else{
                $return['message'] = 'Courier not found';
                $return['error'] = true;
                $this->messageManager->addError(__('Selected courier not found.'));
            }
        } catch (\Exception $e) {
            $return['message'] = $e->getMessage();
            $return['error'] = true;
        }
        return $this->jsonFactory->create()->setData($return);
    }
}
