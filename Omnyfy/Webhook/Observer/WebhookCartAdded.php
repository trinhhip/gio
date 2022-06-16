<?php
namespace Omnyfy\Webhook\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Webhook\Model\WebhookEventSchedule;

class WebhookCartAdded implements ObserverInterface
{
    protected $dateTime;
    protected $storeManager;
    protected $cart;
    protected $vendorFactory;
    protected $helper;
    protected $webhookHelper;
    protected $scheduleFactory;
    protected $logger;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Omnyfy\Webhook\Helper\Data $helper,
        \Omnyfy\Webhook\Helper\WebhookHelper $webhookHelper,
        \Omnyfy\Webhook\Model\WebhookEventScheduleFactory $scheduleFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->cart = $cart;
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
            $quote = $this->cart->getQuote();
            $cartItems = $quote->getAllVisibleItems();
            $type = $this->helper->getWebhookTypeIdByType('cart.added');

            $itemsdata = [];
            foreach ($cartItems as $item) {
                $vendor = $this->vendorFactory->create()->load($item->getVendorId());
                $itemsdata[] = [
                    'sku' => $item->getSku(),
                    'qty' => (int)$item->getQty(),
                    'vendor' => [
                        'object' => 'vendor',
                        'entity_id' => $item->getVendorId(),
                        'name' => $vendor->getName()
                    ]
                ];
            }

            $webhookdata = [
                'event_id' => uniqid(),
                'event_type' => 'cart.added',
                'created_utc' => $this->dateTime->gmtTimestamp(date('Y-m-d H:i:s')),
                'data' => [
                    'entity_id' => $quote->getId(),
                    'object' => 'quote',
                    'items' => $itemsdata,
                    'customer' => [
                        'entity_id' => $quote->getCustomerId(),
                        'object' => 'customer',
                        'email_address' => $quote->getCustomerEmail(),
                        'first_name' => $quote->getCustomerFirstname(),
                        'last_name' => $quote->getCustomerLastname()
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
                        $this->logger->critical(__("Error on cart.added schedule: %1", $e->getMessage()));
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
                        $this->logger->critical(__("Error on cart.added webhook: %1", $e->getMessage()));
                    }
                }
            }
        }
    }

}