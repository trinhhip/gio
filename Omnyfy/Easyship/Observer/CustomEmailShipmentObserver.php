<?php
namespace Omnyfy\Easyship\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomEmailShipmentObserver implements ObserverInterface
{
    protected $orderRepo;
    protected $shipFactory;
    protected $courierCollectionFactory;
    protected $labelFactory;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepo,
        \Omnyfy\Easyship\Model\EasyshipShipmentFactory $shipFactory,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipSelectedCourier\CollectionFactory $courierCollectionFactory,
        \Omnyfy\Easyship\Model\EasyshipShipmentLabelFactory $labelFactory
    ) {
        $this->orderRepo = $orderRepo;
        $this->shipFactory = $shipFactory;
        $this->courierCollectionFactory = $courierCollectionFactory;
        $this->labelFactory = $labelFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getData('transportObject');
        $shipment = $transport->getShipment();

        $orderId = $shipment->getOrderId();
        $locationId = $shipment->getLocationId();
        $order = $this->orderRepo->get($orderId);
        $quoteId = $order->getQuoteId();

        $couriers = $this->courierCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('vendor_location_id', $locationId);

        if ($couriers->count() > 0 && $couriers->getFirstItem()->getCourierId() != null) {
            $selected = $couriers->getFirstItem();
            $courierEntityId = $selected->getEntityId();

            $shipModel =  $this->shipFactory->create()
                ->getEasyshipShipmentIdByParams($orderId, $locationId, $courierEntityId)
            ;

            if($shipModel != null){
                $label = $this->labelFactory->create()->getLabelByShipmentId($shipModel->getEasyshipShipmentId());

                if ($label != null) {
                    $track_url = $label->getTrackingPageUrl();
                    $tracking_number = $label->getTrackingNumber();

                    $transport->setData('track_url', $track_url);
                    $transport->setData('tracking_number', $tracking_number);
                }
            }
        }
    }
}