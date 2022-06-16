<?php
namespace Omnyfy\Webhook\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Webhook\Model\WebhookEventSchedule;

class WebhookShipmentUpdated implements ObserverInterface
{
    protected $storeManager;
    protected $shipmentRepositoryInterface;
    protected $helper;
    protected $webhookHelper;
    protected $scheduleFactory;
    protected $logger;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface,
        \Omnyfy\Webhook\Helper\Data $helper,
        \Omnyfy\Webhook\Helper\WebhookHelper $webhookHelper,
        \Omnyfy\Webhook\Model\WebhookEventScheduleFactory $scheduleFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->storeManager = $storeManager;
        $this->shipmentRepositoryInterface = $shipmentRepositoryInterface;
        $this->helper = $helper;
        $this->webhookHelper = $webhookHelper;
        $this->scheduleFactory = $scheduleFactory;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();

        if ($this->helper->isEnable($storeId)) {
            $shipment = $observer->getEvent()->getShipment();
            $type = $this->helper->getWebhookTypeIdByType('shipment.updated');

            $items = $shipment->getItems();
            $itemsdata = [];
            foreach ($items as $item) {
                $itemsdata[] = [
                    "name" => $item->getName(),
                    "price" => $item->getPrice(),
                    "product_id" => $item->getProductId(),
                    "sku" => $item->getSku(),
                    "weight" => $item->getWeight(),
                    "order_item_id" => $item->getOrderItemId(),
                    "qty" => $item->getQty()
                ];
            }

            $webhookdata = [
                "event_id" => uniqid(),
                "event_type" => "shipment.updated",
                "data" => [
                    "shipment" => [
                        "entity_id" => $shipment->getEntityId(),
                        "increment_id" => $shipment->getIncrementId(),
                        "customer_id" => $shipment->getCustomerId(),
                        "order_id" => $shipment->getOrderId(),
                        "billing_address_id" => $shipment->getBillingAddressId(),
                        "shipping_address_id" => $shipment->getShippingAddressId(),
                        "email_sent" => $shipment->getEmailSent(),
                        "packages" => $shipment->getPackages(),
                        "store_id" => $shipment->getStoreId(),
                        "total_qty" => $shipment->getTotalQty(),
                        "created_at" => $shipment->getCreatedAt(),
                        "updated_at" => $shipment->getUpdatedAt(),
                        "items" => $itemsdata,
                        "tracks" => $this->getShipmentTracks($shipment->getTracks()),
                        "comments" => $this->getShipmentComments($shipment->getComments())
                    ]
                ]
            ];

            $body = json_encode($webhookdata);
            $webhooks = $this->helper->getWebhookByTypeId($type->getId(), $storeId);

            if ($this->helper->isEnableSchedule($storeId)) {
                # Save event and event payload body into “EventSchedule” table
                if (count($webhooks)) {
                    try {
                        foreach ($webhooks as $webhook) {
                            $scheduledata = [
                                'webhook_id' => $webhook->getId(),
                                'body' => $body,
                                'store_id' => $storeId,
                                'status' => WebhookEventSchedule::STATUS_PENDING
                            ];

                            $schedule = $this->scheduleFactory->create();
                            $schedule->setData($scheduledata);
                            $schedule->save();
                        }
                    } catch (\Exception $e) {
                        $this->logger->critical(__("Error on shipment.updated schedule: %1", $e->getMessage()));
                    }
                }

            }else{
                # Use Webhook Helper “Send“ function to dispatch event immediately
                if (count($webhooks)) {
                    try {
                        foreach ($webhooks as $webhook) {
                            $this->webhookHelper->send($body, $webhook->getId());
                        }
                    } catch (\Exception $e) {
                        $this->logger->critical(__("Error on shipment.updated webhook: %1", $e->getMessage()));
                    }
                }
            }
        }
    }

    private function getShipmentTracks($tracks){
        $dataTracks = [];
        if (count($tracks) > 0) {
            foreach ($tracks as $track) {
                $dataTracks[] = [
                    'track_number' => $track->getTrackNumber(),
                    'title' => $track->getTitle(),
                    'carrier_code' => $track->getCarrierCode()
                ];
            }
        }
        return $dataTracks;
    }

    private function getShipmentComments($comments){
        $dataComments = [];
        if (count($comments) > 0) {
            foreach ($comments as $comment) {
                $dataComments[] = $comment->getComment();
            }
        }
        return $dataComments;
    }
}
