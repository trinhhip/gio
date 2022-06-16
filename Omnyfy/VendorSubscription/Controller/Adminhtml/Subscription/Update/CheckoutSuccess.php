<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 10/9/19
 * Time: 5:16 pm
 */
namespace Omnyfy\VendorSubscription\Controller\Adminhtml\Subscription\Update;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\View\Result\PageFactory;
use Omnyfy\VendorSubscription\Model\Source\UpdateStatus;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CheckoutSuccess extends \Omnyfy\VendorSubscription\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorSubscription::subscription_update';
    const COOKIE_NAME = 'Stripe_Checkout_Success';
    const COOKIE_DURATION = 86400; // One day (86400 seconds)

    /**
     * @var \Omnyfy\StripeApi\Helper\Data
     */
    private $apiHelper;
    /**
     * @var \Omnyfy\VendorSubscription\Helper\Data
     */
    private $helper;
    /**
     * @var \Omnyfy\VendorSubscription\Model\SubscriptionFactory
     */
    private $subscriptionFactory;
    /**
     * @var \Omnyfy\VendorSubscription\Model\UpdateFactory
     */
    private $updateFactory;
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        \Omnyfy\StripeApi\Helper\Data $apiHelper,
        \Omnyfy\VendorSubscription\Helper\Data $helper,
        \Omnyfy\VendorSubscription\Model\SubscriptionFactory $subscriptionFactory,
        \Omnyfy\VendorSubscription\Model\UpdateFactory $updateFactory,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        parent::__construct($context, $resultPageFactory, $logger);
        $this->apiHelper = $apiHelper;
        $this->helper = $helper;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->updateFactory = $updateFactory;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    public function execute()
    {
        // retrieve stripe checkout session object
        $checkoutSessionObject = $this->apiHelper->retrieveCheckoutSession($this->getRequest()->getParam('session_id'));
        // load current subscription on magento side
        if(!empty($checkoutSessionObject['customer_details']) && !empty($checkoutSessionObject['subscription'])){
            try{
                $subscriptionId = $checkoutSessionObject['metadata']['subscriptionId'];
                $toPlanGateWayId = $checkoutSessionObject['metadata']['toPlanGateWay'];
                $subscription = $this->subscriptionFactory->create()->load($subscriptionId);
                $fromPlan = $this->helper->loadPlanById($subscription->getPlanId());
                $toPlan = $this->helper->loadPlanByGatewayId($toPlanGateWayId);
                // Update subscription payment data based on checkout session object
                $this->updateSubscription($checkoutSessionObject, $subscription, $fromPlan, $toPlan);
            }catch (\Exception $e){
                $this->messageManager->addErrorMessage("There is something wrong with your request");
                $this->_log->critical($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('omnyfy_vendor/vendor/edit', ['id' => $subscription->getVendorId()]);
            }
            $this->setSuccessStateToCookie();
            return $this->resultRedirectFactory->create()->setPath('omnyfy_vendor/vendor/edit', [
                'id' => $subscription->getVendorId()
            ]);
        }
    }

    private function updateSubscription($checkoutSessionObject, $subscription, $fromPlan, $toPlan)
    {
        $this->createSubscriptionUpdateHistory($subscription, $fromPlan, $toPlan);
        $this->updateSubscriptionInfo($subscription, $checkoutSessionObject, $toPlan);
    }

    private function updateSubscriptionInfo($subscription, $checkoutSessionObject, $toPlan)
    {
        $subscription->setData('plan_id', $toPlan->getId());
        $subscription->setData('plan_name', $toPlan->getPlanName());
        $subscription->setData('plan_price', $toPlan->getPrice());
        $subscription->setData('billing_interval', $toPlan->getInterval());
        $subscription->setData('trial_days', $toPlan->getTrialDays());
        $subscription->getResource()->updateById('gateway_id', $checkoutSessionObject['subscription'], $subscription->getId());
        $subscription->getResource()->updateById('customer_gateway_id', $checkoutSessionObject['customer'], $subscription->getId());
        $subscription->save();
    }

    private function createSubscriptionUpdateHistory($subscription, $fromPlan, $toPlan)
    {
        $update = $this->updateFactory->create();

        //save subscription update model
        if (empty($update->getId())) {
            $updateData = [
                'vendor_id' => $subscription->getVendorId(),
                'subscriptionId' => $subscription->getId(),
                'from_plan_id' => $fromPlan->getId(),
                'from_plan_name' => $fromPlan->getPlanName(),
                'to_plan_id' => $toPlan->getId(),
                'to_plan_name' => $toPlan->getPlanName(),
                'status' => UpdateStatus::STATUS_PENDING
            ];
            $update->addData($updateData);
            $update->save();
        }
    }

    private function setSuccessStateToCookie()
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/')
            ->setDuration(self::COOKIE_DURATION);
        try {
            $this->cookieManager
                ->setPublicCookie(self::COOKIE_NAME, 1, $metadata);
        } catch (InputException|CookieSizeLimitReachedException|FailureToSendException $e) {
            $this->_log->critical($e->getMessage());
        }
    }
}
