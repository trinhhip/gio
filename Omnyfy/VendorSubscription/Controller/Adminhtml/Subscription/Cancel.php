<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 2019-07-12
 * Time: 12:17
 */
namespace Omnyfy\VendorSubscription\Controller\Adminhtml\Subscription;

use Magento\Framework\Exception\LocalizedException;
use Omnyfy\VendorSubscription\Model\Source\SubscriptionStatus;

class Cancel extends \Omnyfy\VendorSubscription\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorSubscription::subscriptions';

    const DATE_FORMAT = 'Y-m-d H:i:s';

    protected $subscriptionFactory;

    protected $qHelper;

    private $planFactory;

    private $timezoneInterface;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Omnyfy\VendorSubscription\Model\SubscriptionFactory $subscriptionFactory,
        \Omnyfy\Core\Helper\Queue $qHelper,
        \Omnyfy\VendorSubscription\Model\PlanFactory $planFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface

    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->qHelper = $qHelper;
        $this->planFactory = $planFactory;
        parent::__construct($context, $resultPageFactory, $logger);
        $this->timezoneInterface = $timezoneInterface;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $vendorId = $this->getRequest()->getParam('vendor_id', null);

        $p = empty($vendorId) ? [] : ['id' => $vendorId];

        try {
            if (empty($id)) {
                throw new LocalizedException(__('No subscription specified.'));
            }

            $subscription = $this->subscriptionFactory->create();
            $subscription->load($id);

            if (empty($subscription->getId()) || $id != $subscription->getId()) {
                throw new LocalizedException(__('Subscription not exist any more.'));
            }

            if ($subscription->getVendorId() != $vendorId) {
                throw new LocalizedException(__('Subscription and vendor mismatched.'));
            }

            if (SubscriptionStatus::STATUS_ACTIVE != $subscription->getStatus()) {
                throw new LocalizedException(__('Subscription is inactive or already been cancelled.'));
            }

            $planId = $subscription->getPlaneId();

            $plan = $this->planFactory->create();
            $plan->load($planId);

            if (!$plan->getId()) {
                throw new LocalizedException(__('Subscription plan is not exist any more.'));
            }
            if (intval($plan->getIsFree())) {
                $currentStoreTime = $this->timezoneInterface->date()->format(self::DATE_FORMAT);
                $subscription->setStatus(SubscriptionStatus::STATUS_CANCELLED);
                $subscription->setExpiryAt($currentStoreTime);
                $subscription->setCancelledAt($currentStoreTime);
                $subscription->save();
                // simulate delete paid subscription action
                $data = [
                    'gateway_id' => $subscription->getGatewayId(),
                    'status' => \Omnyfy\VendorSubscription\Model\Source\SubscriptionStatus::STATUS_DELETED,
                    'cancelFreeSubscription' => 1
                ];

                $this->_eventManager->dispatch('omnyfy_subscription_deleted', [
                    'data' => $data,
                ]);
                $this->messageManager->addSuccessMessage('Subscription has been cancelled');
            }else{
                //add subscription to cancel queue
                $this->qHelper->sendDataToQueue('subscription_cancel', ['subscription_id' => $id]);

                $subscription->setStatus(SubscriptionStatus::STATUS_PENDING_CANCEL);
                $subscription->save();

                $this->messageManager->addSuccessMessage('Added subscription to cancellation queue.');
            }

        }
        catch(LocalizedException $e)
        {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        catch(\Exception $e1)
        {
            $this->messageManager->addErrorMessage(
                __('Something wrong while trying to cancel the subscription. Please review the error log.')
            );

            $this->_log->critical($e1);
        }

        $this->_redirect('omnyfy_vendor/vendor/edit', $p);
    }
}
 