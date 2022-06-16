<?php
namespace Omnyfy\Webhook\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Webhook\Model\WebhookEventSchedule;

class WebhookOrderUpdated implements ObserverInterface
{
    protected $dateTime;
    protected $storeManager;
    protected $vendorFactory;
    protected $helper;
    protected $webhookHelper;
    protected $scheduleFactory;
    protected $logger;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Omnyfy\Webhook\Helper\Data $helper,
        \Omnyfy\Webhook\Helper\WebhookHelper $webhookHelper,
        \Omnyfy\Webhook\Model\WebhookEventScheduleFactory $scheduleFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->vendorFactory = $vendorFactory;
        $this->helper = $helper;
        $this->webhookHelper = $webhookHelper;
        $this->scheduleFactory = $scheduleFactory;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();

        if ($this->helper->isEnable($storeId)) {
            $order = $observer->getEvent()->getOrder();

            #checking time difference between created_at and updated_at
            #because this observer is also called when order first created
            #if the difference is more than 60 seconds, treat it as order.updated
            $createdAt = strtotime($order->getCreatedAt());
            $updatedAt = strtotime($order->getUpdatedAt());
            $diff = $updatedAt - $createdAt;

            if (abs($diff) > 60) {
                $items = $order->getAllVisibleItems();
                $type = $this->helper->getWebhookTypeIdByType('order.updated');
                $shippingAddress = $order->getShippingAddress();

                $itemsdata = [];
                foreach ($items as $item) {
                    $vendor = $this->vendorFactory->create()->load($item->getVendorId());
                    $itemsdata[] = [
                        'sku' => $item->getSku(),
                        'qty' => (int) $item->getQtyOrdered(),
                        'vendor' => [
                            'object' => 'vendor',
                            'entity_id' => $item->getVendorId(),
                            'name' => $vendor->getName()
                        ]
                    ];
                }

                $webhookdata = [
                    'event_id' => uniqid(),
                    'event_type' => 'order.updated',
                    'created_utc' => $this->dateTime->gmtTimestamp(date('Y-m-d H:i:s')),
                    'data' => [
                        'entity_id' => $order->getId(),
                        'object' => 'order',
                        'timestamp' => $this->dateTime->gmtTimestamp($order->getCreatedAt()),
                        'currency' => $order->getOrderCurrencyCode(),
                        'total' => $order->getGrandTotal(),
                        'discount' => $order->getDiscountAmount(),
                        'status' => $order->getStatus(),
                        'payment_status' => $order->getBaseTotalDue() == 0? 'paid':'unpaid',
                        'fulfilment_status' => 'TBC',
                        'items' => $itemsdata,
                        'customer' => [
                            'entity_id' => $order->getCustomerId(),
                            'object' => 'customer',
                            'email_address' => $order->getCustomerEmail(),
                            'first_name' => $order->getCustomerFirstname(),
                            'last_name' => $order->getCustomerLastname()
                        ],
                        'shipping_address' => [
                            'entity_id' => $order->getShippingAddressId(),
                            'city' => $shippingAddress? $shippingAddress->getCity() : "",
                            'street' => $shippingAddress? $shippingAddress->getStreet():"",
                            'country_code' => $shippingAddress? $shippingAddress->getCountryId():"",
                            'postcode' => $shippingAddress? $shippingAddress->getPostcode():"",
                            'first_name' => $shippingAddress? $shippingAddress->getFirstname():"",
                            'last_name' => $shippingAddress? $shippingAddress->getLastname():"",
                            'telephone' => $shippingAddress? $shippingAddress->getTelephone():"",
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
                            $this->logger->critical(__("Error on order.updated schedule: %1", $e->getMessage()));
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
                            $this->logger->critical(__("Error on order.updated webhook: %1", $e->getMessage()));
                        }
                    }
                }
            }
        }
    }

}
