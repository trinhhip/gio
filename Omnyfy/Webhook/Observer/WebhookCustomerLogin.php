<?php
namespace Omnyfy\Webhook\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Webhook\Model\WebhookEventSchedule;

class WebhookCustomerLogin implements ObserverInterface
{
    protected $dateTime;
    protected $storeManager;
    protected $helper;
    protected $webhookHelper;
    protected $scheduleFactory;
    protected $logger;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\Webhook\Helper\Data $helper,
        \Omnyfy\Webhook\Helper\WebhookHelper $webhookHelper,
        \Omnyfy\Webhook\Model\WebhookEventScheduleFactory $scheduleFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->webhookHelper = $webhookHelper;
        $this->scheduleFactory = $scheduleFactory;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();

        if ($this->helper->isEnable($storeId)) {
            $customer = $observer->getEvent()->getCustomer();

            $type = $this->helper->getWebhookTypeIdByType('customer.login');

            $webhookdata = [
                'event_id' => uniqid(),
                'event_type' => 'customer.login',
                'created_utc' => $this->dateTime->gmtTimestamp(date('Y-m-d H:i:s')),
                'data' => [
                    'customer' => [
                        'entity_id' => $customer->getId(),
                        'object' => 'customer',
                        'email_address' => $customer->getEmail(),
                        'first_name' => $customer->getFirstname(),
                        'last_name' => $customer->getLastname()
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
                        $this->logger->critical(__("Error on customer.login schedule: %1", $e->getMessage()));
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
                        $this->logger->critical(__("Error on customer.login webhook: %1", $e->getMessage()));
                    }
                }
            }
        }
    }

}
