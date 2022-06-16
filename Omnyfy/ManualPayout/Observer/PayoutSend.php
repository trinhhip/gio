<?php
namespace Omnyfy\ManualPayout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Mcm\Model\ResourceModel\PayoutType\CollectionFactory as PayoutTypeCollection;
use Omnyfy\Mcm\Model\PayoutType;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\CollectionFactory as VendorPayoutTypeCollection;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout\CollectionFactory as VendorPayoutCollection;
use Omnyfy\VendorSignUp\Model\ResourceModel\VendorKyc\CollectionFactory as KycCollection;
use Omnyfy\VendorSignUp\Model\Source\KycStatus;

class PayoutSend implements ObserverInterface
{
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

    /**
     * @var VendorPayoutTypeCollection
     */
    private $vendorPayoutTypeCollection;

    /**
     * @var VendorPayoutCollection
     */
    private $vendorPayoutCollection;

    /**
     * @var PayoutTypeCollection
     */
    private $payoutTypeCollection;

    /**
     * @var KycCollection
     */
    private $kycCollection;

    /**
     * PayoutSend constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param VendorPayoutTypeCollection $vendorPayoutTypeCollection
     * @param VendorPayoutCollection $vendorPayoutCollection
     * @param PayoutTypeCollection $payoutTypeCollection
     * @param KycCollection $kycCollection
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        VendorPayoutTypeCollection $vendorPayoutTypeCollection,
        VendorPayoutCollection $vendorPayoutCollection,
        PayoutTypeCollection $payoutTypeCollection,
        KycCollection $kycCollection
    ) {
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->vendorPayoutTypeCollection = $vendorPayoutTypeCollection;
        $this->vendorPayoutCollection = $vendorPayoutCollection;
        $this->payoutTypeCollection = $payoutTypeCollection;
        $this->kycCollection = $kycCollection;
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
        $vendorPayout = $this->vendorPayoutCollection->create()->addFieldToFilter('vendor_id', ['eq' => $vendorId])->getFirstItem();
        $vendorPayoutType = $this->vendorPayoutTypeCollection->create()->addFieldToFilter('vendor_id', ['eq' => $vendorId])->getFirstItem();
        $defaultTypeId = $this->payoutTypeCollection->create()->addFieldToFilter('payout_type', ['eq' => PayoutType::DEFAULT_TYPE])->getFirstItem()->getId();
        if ($vendorPayoutType->getPayoutTypeId() != $defaultTypeId) {
            return;
        }
        $payoutTypeId = $vendorPayoutType->getPayoutTypeId();
        $kyc = $this->kycCollection->create()
            ->addFieldToFilter('vendor_id', ['eq' => $vendorId])
            ->getFirstItem();
        if ($kyc->getKycStatus() != KycStatus::STATUS_APPROVED) {
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
                'account_ref' => $vendorPayout->getAccountRef(),
                'amount' => $payoutData['amount'],
                'description' => 'Manual Payout for some vendor',
                'custom_descriptor' => 'This will be displayed on vendor account transaction',
                'ext_info' => json_encode($failExtInfo),
                'reason' => "KYC status hasn't beed approved"
            ];
            $this->eventManager->dispatch(
                'omnyfy_payout_item_fail',
                ['data' => $payoutFailData]
            );
            $this->messageManager->addErrorMessage(__("Vendor with ID %1: KYC status hasn't been approved", $vendorId));
            return;
        }
        try {
            if (empty($payoutData['ext_info']['vendor_order_ids'])) {
                return;
            }
            $successExtInfo = [
                'vendor_id'=> $vendorId,
                'vendor_order_ids'=> $payoutData['ext_info']['vendor_order_ids'],
                'payout_id' => $payoutData['ext_info']['payout_id'],
                'payout_type_id' => $payoutTypeId
            ];
            $payoutSuccessData = [
                'payout_ref' => $payoutData['payout_ref'],
                'account_ref' => $vendorPayout->getAccountRef(),
                'amount' => $payoutData['amount'],
                'description' => 'Manual Payout for some vendor',
                'custom_descriptor' => 'This will be displayed on vendor account transaction',
                'ext_info' => json_encode($successExtInfo),
                'transaction_rebate_ids' => $payoutData['ext_info']['transaction_rebate_ids'],
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
                'account_ref' => $vendorPayout->getAccountRef(),
                'amount' => $payoutData['amount'],
                'description' => 'Manual Payout for some vendor',
                'custom_descriptor' => 'This will be displayed on vendor account transaction',
                'ext_info' => json_encode($failExtInfo),
                'transaction_rebate_ids' => $payoutData['ext_info']['transaction_rebate_ids'],
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
}
