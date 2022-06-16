<?php


namespace OmnyfyCustomzation\ShippingTracking\Observer;


class ShipmentEmail implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    private $template;

    public function __construct(
        \Magento\Framework\View\Element\Template $template
    )
    {
        $this->template = $template;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getData('transportObject');
        $shipment = $transport->getShipment();
        $trackNumber = 'N/A';
        $trackUrl = null;
        $shipmentItems = $this->template->getLayout()->createBlock(
            \Magento\Framework\View\Element\Template::class,
            null,
            [
                'data' => [
                    'items' => $shipment->getItems(),
                    'order' => $transport->getOrder()
                ]
            ]
        )->setTemplate("OmnyfyCustomzation_ShippingTracking::items.phtml")->toHtml();
        foreach ($shipment->getTracks() as $track){
            $trackNumber = $track->getTrackNumber();
            $trackUrl = $track->getTitle();
        }

        $transport->setData('omnyfy_custom_shipment_items', $shipmentItems);
        $transport->setData('shipment_track_number', $trackNumber);
        $transport->setData('shipment_track_url', $trackUrl);
    }
}