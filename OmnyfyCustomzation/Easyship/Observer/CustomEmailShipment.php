<?php

namespace OmnyfyCustomzation\Easyship\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class CustomEmailShipment
 *
 * @package OmnyfyCustomzation\Easyship\Observer
 */
class CustomEmailShipment implements ObserverInterface
{
    protected $orderRepo;
    protected $shipFactory;
    protected $courierCollectionFactory;
    protected $vendorResource;

    /**
     * CustomEmailShipment constructor.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepo
     * @param \Omnyfy\Easyship\Model\EasyshipShipmentFactory $shipFactory
     * @param \Omnyfy\Easyship\Model\ResourceModel\EasyshipSelectedCourier\CollectionFactory $courierCollectionFactory
     * @param \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepo,
        \Omnyfy\Easyship\Model\EasyshipShipmentFactory $shipFactory,
        \Omnyfy\Easyship\Model\ResourceModel\EasyshipSelectedCourier\CollectionFactory $courierCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource
    )
    {
        $this->orderRepo = $orderRepo;
        $this->shipFactory = $shipFactory;
        $this->courierCollectionFactory = $courierCollectionFactory;
        $this->vendorResource = $vendorResource;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
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

        $courierName = $this->getMethod($quoteId, $locationId);
        $shipmentDate = date('Y-m-d', strtotime($shipment->getCreatedAt()));

        if ($couriers->count() > 0 && $couriers->getFirstItem()->getCourierId() != null) {
            $selected = $couriers->getFirstItem();
            $courierEntityId = $selected->getEntityId();

            $shipModel = $this->shipFactory->create()
                ->getEasyshipShipmentIdByParams($orderId, $locationId, $courierEntityId);

            if ($shipModel != null) {
                $courierName = $shipModel->getCourierName();
                $shipmentDate = date('Y-m-d', strtotime($shipModel->getCreatedAt()));
            }
        }
        $transport->setData('courier_name', $this->getShortCourierName($courierName));
        $transport->setData('shipping_method', $courierName);
        $transport->setData('shipment_date', $shipmentDate);
    }

    /**
     * @param $quoteId
     * @param $locationId
     *
     * @return |null
     */
    public function getQuoteShipping($quoteId, $locationId)
    {
        if (empty($locationId)) {
            return null;
        }

        $shipping = $this->vendorResource->getQuoteShipping($quoteId);
        if (count($shipping) > 0) {
            foreach ($shipping as $value) {
                if ($value['location_id'] == $locationId) {
                    return $value;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * @param $quoteId
     * @param $locationId
     *
     * @return string
     */
    protected function getMethod($quoteId, $locationId)
    {
        $method = $this->getQuoteShipping($quoteId, $locationId);
        if (!empty($method)) {
            $courierName = $method['carrier'] . ' - ' . $method['method_title'];
        } else {
            $courierName = 'Flat rate';
        }

        return $courierName;
    }

    /**
     * @param $courierName
     *
     * @return mixed
     */
    protected function getShortCourierName($courierName)
    {
        if (strstr(strtolower($courierName), '(')) {
            list($courierName, $e) = explode('(', $courierName);
        }
        return $courierName;
    }
}