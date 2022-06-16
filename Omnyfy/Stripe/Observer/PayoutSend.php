<?php
namespace Omnyfy\Stripe\Observer;

use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Mcm\Model\PayoutType;
use Omnyfy\Mcm\Model\ResourceModel\PayoutType\CollectionFactory as PayoutTypeCollection;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\CollectionFactory as VendorPayoutTypeCollection;
use Omnyfy\Stripe\Helper\Gateway;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout\CollectionFactory as VendorPayoutCollection;

class PayoutSend implements ObserverInterface
{
    /**
     * @var \Omnyfy\Stripe\Model\ResourceModel\VendorConnectAccount
     */
    private $vendorConnectAccount;

    /**
     * @var Gateway
     */
    private $gatewayHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \StripeIntegration\Payments\Helper\Subscriptions
     */
    private $subscriptionsHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    private $vendorPayoutTypeCollection;

    private $vendorPayoutCollection;

    private $payoutTypeCollection;

    /**
     * PayoutSend constructor.
     * @param \Omnyfy\Stripe\Model\ResourceModel\VendorConnectAccount $vendorConnectAccount
     * @param Gateway $gatewayHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \StripeIntegration\Payments\Helper\Subscriptions $subscriptionsHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Omnyfy\Stripe\Model\ResourceModel\VendorConnectAccount $vendorConnectAccount,
        \Omnyfy\Stripe\Helper\Gateway $gatewayHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptionsHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        VendorPayoutTypeCollection $vendorPayoutTypeCollection,
        VendorPayoutCollection $vendorPayoutCollection,
        PayoutTypeCollection $payoutTypeCollection
    ) {
        $this->vendorConnectAccount = $vendorConnectAccount;
        $this->gatewayHelper = $gatewayHelper;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->storeManager = $storeManager;
        $this->subscriptionsHelper = $subscriptionsHelper;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->vendorPayoutTypeCollection = $vendorPayoutTypeCollection;
        $this->vendorPayoutCollection = $vendorPayoutCollection;
        $this->payoutTypeCollection = $payoutTypeCollection;
    }

    /**
     * Create Stripe transfer for vendor
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payoutData = $observer->getData('data');
        $vendorId = $payoutData['ext_info']['vendor_id'];
        $vendorStripeAccountId = $this->vendorConnectAccount->getStripeAccountIdByVendorId($vendorId);
        $vendorPayoutType = $this->vendorPayoutTypeCollection->create()->addFieldToFilter('vendor_id', ['eq' => $vendorId])->getFirstItem();
        $defaultTypeId = $this->payoutTypeCollection->create()->addFieldToFilter('payout_type', ['eq' => PayoutType::DEFAULT_TYPE])->getFirstItem()->getId();
        if ($vendorPayoutType->getPayoutTypeId() == $defaultTypeId) {
            return;
        }
        $payoutTypeId = $vendorPayoutType->getPayoutTypeId();
        try {
            if (empty($payoutData['ext_info']['vendor_order_ids'])) {
                return;
            }
            /**
             * Create transfer for sucController/Adminhtml/PendingPayouts/MassProcessStripePayouts.phpcess payout item
             */
            if ($payoutData['amount'] > 0) {
                $baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrency()->getCode();

                $orderCollection = $this->orderCollectionFactory->create()->addFieldToFilter(
                    'entity_id',
                    ['in' => $this->vendorConnectAccount->getOrderIdFromVendorOrders($payoutData['ext_info']['vendor_order_ids'])]
                );
                /**
                 * To calculate total amount of the orders that use other payment method
                 */
                foreach ($orderCollection->getItems() as $order) {
                    $params = ['transfer_group' => $payoutData['payout_ref']];
                    $charce = $this->gatewayHelper->retrieveChargeByOrder($order);
                    if (empty($charce->transfer_group)) {
                        $this->gatewayHelper->updateChargeByOrder($order, $params);
                    }
                }
                $transferData = [
                    "amount" => $this->convertMagentoAmountToStripeAmount($payoutData['amount'], $baseCurrencyCode),
                    "currency" => $baseCurrencyCode,
                    "transfer_group" => $payoutData['payout_ref'],
                    "destination" => $vendorStripeAccountId,
                    "description" => $payoutData['description']
                ];
                $transfer = $this->gatewayHelper->createTransfer($transferData);
            }
            $successExtInfo = [
                'vendor_id'=> $vendorId,
                'vendor_order_ids'=> $payoutData['ext_info']['vendor_order_ids'],
                'payout_id' => $payoutData['ext_info']['payout_id'],
                'payout_type_id' => $payoutTypeId
            ];
            $payoutSuccessData = [
                'payout_ref' => $payoutData['payout_ref'],
                'account_ref' => $vendorStripeAccountId,
                'amount' => $payoutData['amount'],
                'description' => 'Payout for some vendor',
                'custom_descriptor' => 'This will be displayed on vendor account transaction',
                'ext_info' => json_encode($successExtInfo),
                'state' => 'completed'
            ];
            $this->eventManager->dispatch(
                'omnyfy_payout_item_change',
                ['data' => $payoutSuccessData]
            );
        } catch (\Exception $e) {
            /**
             * Dispatch event for failed payout item because of exception
             */
            $failExtInfo = [
                'vendor_id' => $vendorId,
                'vendor_order_ids' => $payoutData['ext_info']['vendor_order_ids'],
                'payout_id' => $payoutData['ext_info']['payout_id'],
                'payout_type_id' => $payoutTypeId
            ];
            $payoutFailData = [
                'payout_ref' => $payoutData['payout_ref'],
                'account_ref' => $vendorStripeAccountId,
                'amount' => $payoutData['amount'],
                'description' => 'Payout for some vendor',
                'custom_descriptor' => 'This will be displayed on vendor account transaction',
                'ext_info' => json_encode($failExtInfo),
                'reason' => $e->getMessage()
            ];
            $this->eventManager->dispatch(
                'omnyfy_payout_item_fail',
                ['data' => $payoutFailData]
            );
            $this->messageManager->addErrorMessage(__("Vendor with ID %1: " . $e->getMessage(), $vendorId));
            $this->logger->critical($e->getMessage());
        }

    }

    public function convertMagentoAmountToStripeAmount($amount, $currency)
    {
        $cents = 100;
        if ($this->isZeroDecimal($currency))
            $cents = 1;

        return round($amount * $cents);
    }

    public function isZeroDecimal($currency)
    {
        return in_array(strtolower($currency), array(
            'bif', 'djf', 'jpy', 'krw', 'pyg', 'vnd', 'xaf',
            'xpf', 'clp', 'gnf', 'kmf', 'mga', 'rwf', 'vuv', 'xof'));
    }
}