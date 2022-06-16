<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 2019-07-12
 * Time: 14:27
 */
namespace Omnyfy\VendorSubscription\Observer;

class SubscriptionDelete implements \Magento\Framework\Event\ObserverInterface
{
    protected $helper;

    protected $_logger;
    /**
     * @var \Omnyfy\VendorSubscription\Helper\Email
     */
    private $emailHelper;
    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;

    public function __construct(
        \Omnyfy\VendorSubscription\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Omnyfy\VendorSubscription\Helper\Email $emailHelper,
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->helper = $helper;
        $this->_logger = $logger;
        $this->emailHelper = $emailHelper;
        $this->eventManager = $eventManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getData('data');
        $subscription = $this->helper->loadSubscriptionByGatewayId($data['gateway_id']);
        if (empty($subscription)) {
            $this->_logger->error('Missing subscription', $data);
            return;
        }

        // Check if stripe subscription is delete because of switching to free plan on Magento side
        $currentPlan = $this->helper->loadPlanById($subscription->getPlanId());
        if(!$currentPlan->getIsFree() || !empty($data['cancelFreeSubscription'])){
            $subscription->addData($data);
            $subscription->save();

            $this->helper->disableVendor($subscription->getVendorId());
            $this->helper->disableProductsByVendorId($subscription->getVendorId());
            $this->emailHelper->sendSubscriptionExpiry($subscription);
        }else{
            $this->triggerInvoiceSuccessForFreePlan($subscription);
            $this->emailHelper->sendUpdateSubscription($subscription);
        }
    }

    private function triggerInvoiceSuccessForFreePlan($subscription){
        try {
            $data = [
                'plan_gateway_id' => $subscription->getPlanGatewayId(),
                'sub_gateway_id' => 'FREE_SUB_'. $subscription->getVendorId(),
                'billing_date' => date('Y-m-d H:i:s'),
                'start_date' => date('Y-m-d H:i:s'),
                'end_date' => null,
                'billing_account_name' => $subscription->getVendorName(),
                'billing_amount' => 0.0,
                'status' => \Omnyfy\VendorSubscription\Model\Source\HistoryStatus::STATUS_SUCCESS,
                'invoice_link' => null
            ];
            $subscription->setData('gateway_id', $data['sub_gateway_id']);
            $subscription->save();
            $this->eventManager->dispatch('omnyfy_subscription_invoice_succeeded',
                [
                    'data' => $data
                ]
            );
        }
        catch(\Exception $e) {
            $this->_log->critical($e->getMessage());
        }
    }
}
