<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 12/9/19
 * Time: 11:41 pm
 */
namespace Omnyfy\VendorSubscription\Controller\Adminhtml\Subscription\Update;

use Omnyfy\VendorSubscription\Model\Source\UpdateStatus;

class Save extends \Omnyfy\VendorSubscription\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorSubscription::subscription_update';

    protected $coreRegistry;

    protected $_gwHelper;

    protected $updateFactory;

    protected $dataHelper;

    protected $subscriptionFactory;
    /**
     * @var \Omnyfy\VendorSubscription\Helper\Email
     */
    private $emailHelper;
    /**
     * @var \Omnyfy\StripeApi\Helper\Data
     */
    private $apiHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Omnyfy\Core\Helper\Queue
     */
    private $qHelper;
    /**
     * @var Magento\Backend\Helper\Data
     */
    private $backendHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $coreRegistry,
        \Omnyfy\VendorSubscription\Helper\GatewayInterface $_gwHelper,
        \Omnyfy\VendorSubscription\Model\UpdateFactory $updateFactory,
        \Omnyfy\VendorSubscription\Helper\Data $dataHelper,
        \Omnyfy\VendorSubscription\Model\SubscriptionFactory $subscriptionFactory,
        \Omnyfy\VendorSubscription\Helper\Email $emailHelper,
        \Omnyfy\StripeApi\Helper\Data $apiHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\Core\Helper\Queue $qHelper,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->_gwHelper = $_gwHelper;
        $this->updateFactory = $updateFactory;
        $this->dataHelper = $dataHelper;
        $this->subscriptionFactory = $subscriptionFactory;
        parent::__construct($context, $resultPageFactory, $logger);
        $this->emailHelper = $emailHelper;
        $this->apiHelper = $apiHelper;
        $this->_storeManager = $storeManager;
        $this->qHelper = $qHelper;
        $this->backendHelper = $backendHelper;
    }

    public function execute()
    {
        //validation
        $id = $this->getRequest()->getParam('id');
        $subscription = $this->loadSubscription($this->getRequest());
        if (empty($id) || empty($subscription)) {
            $this->messageManager->addErrorMessage('Wrong data provided');
            return $this->resultRedirectFactory->create()->setPath('omnyfy_vendor/vendor/index');
        }

        $vendorId = $subscription->getVendorId();

        try{
            $data = $this->getRequest()->getPostValue();
            $inputFilter = new \Zend_Filter_Input([], [], $data);
            $data = $inputFilter->getUnescaped();

            if (!isset($data['to_plan_id']) || empty($data['to_plan_id'])) {
                $this->messageManager->addErrorMessage('Something wrong with form submit.');
                return $this->resultRedirectFactory->create()->setPath('omnyfy_subscription/subscription_update/edit', ['id' => $id]);
            }

            $toPlanId = $data['to_plan_id'];

            //check if toPlan assigned to this vendor type
            $planIdToRoleId = $this->dataHelper->getRoleIdsMapByVendorTypeId($subscription->getVendorTypeId());
            if (!array_key_exists($toPlanId, $planIdToRoleId)) {
                $this->messageManager->addErrorMessage('Selected plan is not for vendor type '. $subscription->getVendorTypeId());
                return $this->resultRedirectFactory->create()->setPath('omnyfy_subscription/subscription_update/edit', ['id' => $id]);
            }

            $fromPlan = $this->dataHelper->loadPlanById($subscription->getPlanId());
            $toPlan = $this->dataHelper->loadPlanById($data['to_plan_id']);

            // Check if toPlan is set limit product and current Vendor's number of products are greater than toPlan limit products
            $vendorProducts = $this->dataHelper->getVendorProducts($vendorId);
            $numberOfProducts = $toPlan->getProductLimit() - $vendorProducts;
            if ($toPlan->getIsLimitProduct() && $numberOfProducts < 0) {
                $this->messageManager->addErrorMessage('The selected subscription plan only allows '. $toPlan->getProductLimit() .' products. Please delete atleast ' . abs($numberOfProducts) . ' products from your catalog to change plan.');
                return $this->resultRedirectFactory->create()->setPath('omnyfy_vendor/vendor/edit', ['id' => $vendorId]);
            }

            if (empty($fromPlan) || empty($toPlan)) {
                $this->messageManager->addErrorMessage('Related plan does not exist any more');
                return $this->resultRedirectFactory->create()->setPath('omnyfy_subscription/subscription_update/edit', ['id' => $id]);
            }

            // Change plan from free to paid
            if($fromPlan->getIsFree() && !$toPlan->getIsFree()){
                $data = [
                    'line_items[0][price]' => $toPlan->getGatewayId(),
                    'line_items[0][quantity]' => 1,
                    'mode' => 'subscription',
                    'customer_email' => $subscription->getVendorEmail(),
                    'metadata[toPlanGateWay]' => $toPlan->getGatewayId(),
                    'metadata[subscriptionId]' => $id,
                    'success_url' => $this->backendHelper->getUrl('omnyfy_subscription/subscription_update/checkoutsuccess/') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $this->backendHelper->getUrl('omnyfy_subscription/subscription_update/checkoutsuccess/') . '?session_id={CHECKOUT_SESSION_ID}',
                ];
                $checkoutSessionObject = $this->apiHelper->createCheckoutSession($data);
                if(empty($checkoutSessionObject['error']) && !empty($checkoutSessionObject['url'])){
                    return $this->resultRedirectFactory->create()->setPath($checkoutSessionObject['url']);
                }
                $this->messageManager->addErrorMessage($checkoutSessionObject['error']['message']);
                return $this->resultRedirectFactory->create()->setPath('omnyfy_subscription/subscription_update/edit', ['id' => $id]);
            }

            // Change plan from paid to free
            if (!$fromPlan->getIsFree() && $toPlan->getIsFree()) {
                // Change subscription plan on Magento side
                $this->saveSubscriptionPlan($subscription, $toPlan);
                //add subscription to cancel queue to cancel it on Stripe
                $this->qHelper->sendDataToQueue('subscription_cancel', ['subscription_id' => $id]);
                $this->messageManager->addSuccessMessage('Your subscription updated to the plan you selected');
                return $this->resultRedirectFactory->create()->setPath('omnyfy_vendor/vendor/edit', ['id' => $vendorId]);
            }


            $update = $this->saveSubscriptionHistory($subscription, $vendorId, $fromPlan, $toPlan);

            // Change Plan from free to free
            if ($fromPlan->getIsFree() && $toPlan->getIsFree()) {
                $subscription->load($subscription->getId());
                $this->saveSubscriptionPlan($subscription, $toPlan);
                $this->dataHelper->saveUpdateStatus($update->getId(), UpdateStatus::STATUS_DONE);
                $freeSubscriptionData = [
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

                $this->_eventManager->dispatch('omnyfy_subscription_invoice_succeeded',
                    [
                        'data' => $freeSubscriptionData
                    ]
                );
                $this->messageManager->addSuccessMessage('Your subscription updated to the plan you selected');
                return $this->resultRedirectFactory->create()->setPath('omnyfy_vendor/vendor/edit', ['id' => $vendorId]);
            }

            $result = 1;
            //send request when pending or failed
            if (UpdateStatus::STATUS_DONE != $update->getStatus()) {
                $result = $this->_gwHelper->changePlan(
                    $subscription->getGatewayId(),
                    $fromPlan->getGatewayId(),
                    $toPlan->getGatewayId()
                );
            }

            if (empty($result)) {
                $this->dataHelper->saveUpdateStatus($update->getId(), UpdateStatus::STATUS_FAILED);
                $this->messageManager->addErrorMessage('Failed to change plan');
            }
            else {
                $subscription->load($subscription->getId());
                $subscription->setData('plan_id', $toPlan->getId());
                $subscription->setData('plan_name', $toPlan->getPlanName());
                $subscription->setData('plan_price', $toPlan->getPrice());
                $subscription->setData('billing_interval', $toPlan->getInterval());
                $subscription->setData('trial_days', $toPlan->getTrialDays());
                $subscription->save();
                // $this->emailHelper->sendUpdateSubscription($subscription);
                $this->dataHelper->saveUpdateStatus($update->getId(), UpdateStatus::STATUS_DONE);
                $this->messageManager->addSuccessMessage('Your subscription updated to the plan you selected');
            }

            return $this->resultRedirectFactory->create()->setPath('omnyfy_vendor/vendor/edit', ['id' => $vendorId]);
        }
        catch(\Exception $e) {
            $this->_log->debug($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('omnyfy_subscription/subscription_update/edit', ['id' => $id]);
        }
    }

    protected function loadSubscription($request)
    {
        $subscriptionId = intval($request->getParam('id'));

        $subscription = $this->subscriptionFactory->create();
        if ($subscriptionId) {
            try {
                $subscription->load($subscriptionId);
            }
            catch (\Exception $e) {
                $this->_log->critical($e);
            }
        }

        $this->coreRegistry->register('current_omnyfy_subscription_subscription', $subscription);
        return $subscription;
    }

    /**
     * @param \Omnyfy\VendorSubscription\Model\Subscription $subscription
     * @param \Omnyfy\VendorSubscription\Model\Plan $toPlan
     * @return void
     * @throws \Exception
     */
    public function saveSubscriptionPlan(
        \Omnyfy\VendorSubscription\Model\Subscription $subscription,
        \Omnyfy\VendorSubscription\Model\Plan $toPlan
    ) {
        $subscription->setData('plan_id', $toPlan->getId());
        $subscription->setData('plan_name', $toPlan->getPlanName());
        $subscription->setData('plan_price', $toPlan->getPrice());
        $subscription->setData('billing_interval', $toPlan->getInterval());
        $subscription->setData('trial_days', $toPlan->getTrialDays());
        $subscription->setData('plan_gateway_id', $toPlan->getGatewayId());
        $subscription->save();
    }

    private function saveSubscriptionHistory($subscription, $vendorId, $fromPlan, $toPlan)
    {
        $update = $this->updateFactory->create();

        //save subscription update model
        if (empty($update->getId())) {
            $updateData = [
                'vendor_id' => $vendorId,
                'subscription_id' => $subscription->getId(),
                'from_plan_id' => $fromPlan->getId(),
                'from_plan_name' => $fromPlan->getPlanName(),
                'to_plan_id' => $toPlan->getId(),
                'to_plan_name' => $toPlan->getPlanName(),
                'status' => UpdateStatus::STATUS_PENDING
            ];
            $update->addData($updateData);
            $update->save();
        }
        return $update;
    }
}
