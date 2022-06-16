<?php
namespace Omnyfy\ManualPayout\Controller\Adminhtml\PendingPayouts;

use Magento\Ui\Component\MassAction\Filter;
use Omnyfy\Mcm\Api\VendorPayoutInterface;
use Omnyfy\Mcm\Model\ResourceModel\PayoutType\CollectionFactory as PayoutTypeCollection;
use Omnyfy\Mcm\Model\ResourceModel\VendorOrder\CollectionFactory as VendorOrderCollectionFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout\CollectionFactory as VendorPayoutCollection;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout\CollectionFactory as VendorPayoutCollectionFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\CollectionFactory as VendorPayoutTypeCollection;
use Omnyfy\Mcm\Model\SequenceFactory;
use Omnyfy\Mcm\Model\VendorPayoutHistoryFactory;
use Omnyfy\Mcm\Model\VendorPayoutInvoice\VendorPayoutInvoiceOrderFactory;
use Omnyfy\Mcm\Model\VendorPayoutInvoiceFactory;
use Omnyfy\RebateCore\Model\Repository\TransactionRebateRepository;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;

class MassProcessManualPayouts extends \Omnyfy\Mcm\Controller\Adminhtml\PendingPayouts\MassProcessPayouts
{
    const PAYOUT_TYPE = "Manual";

    protected $payoutTypeCollection;

    protected $vendorPayoutTypeCollection;

    protected $vendorPayoutCollection;

    protected $emptyMessage = "No Payouts Processed.";

    protected $transactionRebateRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**

     * @var VendorPayoutInterface

     */

    private $vendorPayout;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        Filter $filter,
        VendorPayoutCollectionFactory $vendorPayoutCollectionFactory,
        VendorOrderCollectionFactory $vendorOrderCollectionFactory,
        VendorPayoutHistoryFactory $vendorPayoutHistoryFactory,
        SequenceFactory $sequenceFactory,
        VendorPayoutInvoiceFactory $vendorPayoutInvoiceFactory,
        VendorPayoutInvoiceOrderFactory $vendorPayoutInvoiceOrderFactory,
        \Omnyfy\Mcm\Model\Config $config,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Mcm\Helper\Payout $payoutHelper,
        PayoutTypeCollection $payoutTypeCollection,
        VendorPayoutTypeCollection $vendorPayoutTypeCollection,
        VendorPayoutCollection $vendorPayoutCollection,
        TransactionRebateRepository $transactionRebateRepository,
        \Omnyfy\Mcm\Model\ResourceModel\VendorOrderFactory $vendorOrderResourceFactory,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        VendorPayoutInterface $vendorPayout,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource
    ) {
        $this->payoutTypeCollection = $payoutTypeCollection;
        $this->vendorPayoutTypeCollection = $vendorPayoutTypeCollection;
        $this->vendorPayoutCollection = $vendorPayoutCollection;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger, $filter, $vendorPayoutCollectionFactory, $vendorOrderCollectionFactory, $vendorPayoutHistoryFactory, $sequenceFactory, $vendorPayoutInvoiceFactory, $vendorPayoutInvoiceOrderFactory, $config, $mcmHelper, $resourceConnection, $orderRepository, $payoutHelper, $transactionRebateRepository, $vendorOrderResourceFactory, $orderItemRepository, $vendorRepository, $scopeConfig, $vendorPayoutTypeCollection, $feesManagementResource);
        $this->vendorPayout = $vendorPayout;
    }
    public function execute() {
        try {
            $collection = $this->filter->getCollection($this->vendorPayoutCollectionFactory->create());

            foreach($collection as $payout) {
                $accountRef = $payout->getAccountRef();
                if (empty($accountRef)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Vendor with ID %1 don\'t have a valid AccountRef', $payout->getVendorId())
                    );
                }

                // If KYC is set not to process on platform don't check third party account id
                if ($this->_config->isIncludeKyc()) {
                    $thirdPartyAccountId = $payout->getThirdPartyAccountId();
                    if (empty($thirdPartyAccountId)) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Vendor with ID %1 don\'t have a valid Account ID in gateway', $payout->getVendorId())
                        );
                    }
                }
            }
            $vendorOrderCollection = $this->vendorOrderCollectionFactory->create();
            $vendorOrderCollection = $vendorOrderCollection->addFieldToFilter('vendor_id', ['in' => $collection->getColumnValues('vendor_id')])
                ->addFieldToFilter('payout_status', ['in' => [0,3]])
                ->addFieldToFilter('payout_action', 1);
	    
	    if (empty($vendorOrderCollection->getSize())) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('No payout has been processed as the selected vendors do not have any Pending Payout amounts')
                );
            }
            $payoutsUpdated = 0;
            $payoutRef = $this->getRef();
            foreach ($collection as $payout) {
                $totalVendorOrdersUpdated = $this->vendorPayoutsProcess($payout, $payoutRef); //$payout->getVendorId(), $payout->getPayoutId(), $payout->getAccountRef()
                //$payout->setEwalletBalance($payout->getEwalletBalance() + $ewalletBalance);
                $payout->save();
                $payoutsUpdated += $totalVendorOrdersUpdated;
            }

            if ($payoutsUpdated) {
                $this->setLastRef($payoutRef);
                $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $payoutsUpdated));
            } elseif ($payoutsUpdated == 0) {
                $this->messageManager->addErrorMessage(__($this->emptyMessage));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while process payouts data. Please review the error log.')
            );
            $this->setLastRef($payoutRef);
            $this->_logger->critical($e->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('omnyfy_mcm/pendingpayouts/index');
        return $resultRedirect;
    }

    protected function getPayoutTypeId($payoutType = null)
    {
        $payoutType = $payoutType === null ? self::PAYOUT_TYPE : $payoutType;
        $typeId = $this->payoutTypeCollection->create()->addFieldToFilter('payout_type', ['eq' => $payoutType])->getFirstItem()->getId();
        return $typeId;
    }

    protected function getVendorPayoutType($vendorId)
    {
        $vendorPayoutType = $this->vendorPayoutTypeCollection->create()
            ->addFieldToFilter('vendor_id', ['eq' => $vendorId])->getFirstItem();
        return $vendorPayoutType->getPayoutTypeId();
    }

    protected function isProcessPayoutForStripe($vendorId)
    {
        $stripePayoutTypeId = $this->getPayoutTypeId("Stripe");
        $currentVendorPayoutTypeId = $this->getVendorPayoutType($vendorId);

        if (!empty($stripePayoutTypeId) && ($currentVendorPayoutTypeId == $stripePayoutTypeId)) {
            return true;
        }
        return false;
    }

    public function vendorManualPayoutsProcess($payout, $payoutRef) {
        $vendorTotalPayoutAmount = 0.00;
        $vendorPayoutInvoiceData = [
            'payout_ref' => $payoutRef,
            'increment_id' => $this->getRef('invoice_ref'),
            'vendor_id' => $payout->getVendorId(),
            'orders_total_incl_tax' => 0.00,
            'orders_total_tax' => 0.00,
            'fees_total_incl_tax' => 0.00,
            'fees_total_tax' => 0.00,
            'shipping_total_for_order' => 0.00
        ];
        $vendorId = $payout->getVendorId();
        $vendorName = $payout->getVendorName();
        $payoutId = $payout->getPayoutId();
        $accountRef = $payout->getAccountRef();
        $vendorOrderCollection = $this->getVendorOrderCollection($vendorId);
        $updatedOrderCount = 0;

        // vendor collection will always not be empty, ensure that there is records
        if (!empty($vendorOrderCollection) && count($vendorOrderCollection) > 0) {
            $vendorOrderIds = [];
            $transactionIds = [];

            foreach ($vendorOrderCollection as $vendorOrder) {
                $updatedOrderCount++;
                $vendorOrder->setPayoutStatus(4); //Payout status 4 = Processed - awaiting settlement
                $vendorOrder->save();
                //manage payout history

                // If there is a record in omnyfy_mcm_shipping_calculation use that for order
                $doesShippingMcmCalculationExist = $this->payoutHelper->doesShippingCalculationExist($vendorOrder);
                if ($doesShippingMcmCalculationExist) {
                    $orderTotalIncludingShippingPayout = $this->payoutHelper->getOrderPayoutShippingAmount($vendorOrder);
                    $vendorOrderTotalIncTax = ($vendorOrder->getBaseGrandTotal() + ($orderTotalIncludingShippingPayout - $vendorOrder->getShippingDiscountAmount()));
                    $vendorPayoutInvoiceData['shipping_total_for_order'] = $orderTotalIncludingShippingPayout - $vendorOrder->getShippingDiscountAmount();
                } else {
                    // fallback for existing
                    $vendorOrderTotalIncTax = ($vendorOrder->getBaseGrandTotal() + ($vendorOrder->getBaseShippingAmount() + $vendorOrder->getBaseShippingTax() - $vendorOrder->getShippingDiscountAmount()));
                }

                $vendorOrderTotalFees = $vendorOrder->getTotalCategoryFee() + $vendorOrder->getTotalSellerFee() + $vendorOrder->getDisbursementFee();
                $vendorOrderTotalFeeTax = $vendorOrder->getTotalCategoryFeeTax() + $vendorOrder->getTotalSellerFeeTax() + $vendorOrder->getDisbursementFeeTax();
                $vendorOrderFeeTotalIncTax = $vendorOrderTotalFees + $vendorOrderTotalFeeTax;


                // This needs to take into account the shipping fees
                $payoutAmount = $vendorOrderTotalIncTax - $vendorOrderFeeTotalIncTax;
                $rebateOrder = $this->transactionRebateRepository->getMaturedVendorRebateTransactions($vendorOrder->getVendorId(), $vendorOrder->getOrderId());
                if ($this->_config->getEnableWholeSale()) {
                    $orderId = $vendorOrder->getOrderId();
                    $vendorId = $vendorOrder->getVendorId();
                    if ($this->getVendorPayoutBasisType($vendorId) == PayoutBasisType::WHOLESALE_VENDOR_VALUE) {
                        $payoutAmount = $this->getTotalOrderByWholesaleVendor($orderId, $vendorId);
                    }
                }

                if (isset($rebateOrder['rebate_transactions'])) {
                    foreach ($rebateOrder['rebate_transactions'] as $transaction) {
                        $transcationsId = $transaction['entity_id'];
                        $this->transactionRebateRepository->startProcess($transcationsId);
                        $payoutAmount -= $transaction['rebate_total_amount'];
                        $transactionIds[$vendorOrder->getId()][] = $transcationsId;
                    }
                }

                // If MCM is set not to manage shipping fees, if ship by type is also disabled, pay out to vendor
                if (!$this->mcmHelper->getShipByTypeConfiguration()) {
                    if (!$this->mcmHelper->manageShippingFees()) {
                        $orderShippingAmount = $this->getOrderShippingFee($vendorOrder);

                        if (isset($orderShippingAmount['amount'])) {
                            $payoutAmount += $orderShippingAmount['amount'];
                        }
                    }
                }
                $payoutAmount = $vendorOrder->getPayoutAmount() > 0 ? $payoutAmount : 0;

                $vendorPayoutHistoryData = [
                    'payout_id' => $payoutId,
                    'vendor_id' => $vendorId,
                    'vendor_order_id' => $vendorOrder->getId(),
                    'payout_ref' => $payoutRef,
                    'payout_amount' => $payoutAmount, //$vendorOrder->getPayoutAmount(),
                    'status' => 1 // 3 = In progress, 4 = Processed - awaiting settlement
                ];

                $items = $this->vendorOrderResourceFactory->create()->getOrderItems($vendorOrder->getId(), $vendorId);
                foreach ($items as $item) {
                    $vendorPayoutInvoiceData['category_commission'] += $item['category_fee'];
                }

                $this->saveVendorPayoutHistory($vendorPayoutHistoryData);
                $vendorOrderIds[] = $vendorOrder->getId();
                $vendorTotalPayoutAmount += $payoutAmount;
                $vendorPayoutInvoiceData['orders_total_incl_tax'] += $vendorOrderTotalIncTax;
                $vendorPayoutInvoiceData['orders_total_tax'] += $vendorOrder->getBaseTaxAmount();
                $vendorPayoutInvoiceData['fees_total_incl_tax'] += $vendorOrderFeeTotalIncTax;
                $vendorPayoutInvoiceData['fees_total_tax'] += $vendorOrderTotalFeeTax;

            }


            /*
             * Integrate with assembly pay for send payout
             */
            $eventData = [
                'payout_ref' => $payoutRef,
                'account_ref' => $accountRef,
                'amount' => $this->totalEarning($vendorOrderCollection),
                'description' => 'Payout for ' . $vendorName,
                'custom_descriptor' => 'This will be displayed on vendor account transaction',
                'ext_info' => [
                    'vendor_id' => $vendorId,
                    'payout_id' => $payoutId,
                    'vendor_order_ids' => $vendorOrderIds,
                    'transaction_rebate_ids' => $transactionIds
                ],
            ];
            $this->_eventManager->dispatch('omnyfy_mcm_payout_send', ['data' => $eventData]);

            /**
             * Save vendor payout invoice
             */
            $vendorPayoutInvoiceData['total_earning_incl_tax'] = $vendorTotalPayoutAmount;
            $this->saveVendorPayoutInvoice($vendorPayoutInvoiceData, $vendorOrderCollection);
            $this->setLastRef($vendorPayoutInvoiceData['increment_id'], 'invoice_ref');
        }

        // get number of orders that are not going to be processed
        $vendorOrderCollectionNonStripe = $this->getVendorOrderCollection($vendorId);
        $vendorOrderCollectionNonStripe->getSelect()
            ->join(
                ['payment' => $vendorOrderCollection->getTable('sales_order_payment')],
                'payment.parent_id = main_table.order_id',
                ['method']
            )->where('method like ?', 'stripe_payments');

        if (!empty($vendorOrderCollectionNonStripe) && count ($vendorOrderCollectionNonStripe) > 0) {
            foreach ($vendorOrderCollectionNonStripe as $nonManualOrder) {
                $this->messageManager->addErrorMessage('Order: ' . $nonManualOrder->getOrderIncrementId() . ' could not be processed. This payout has been paid using Stripe and the Vendor has a Stripe payout type, please select Process Stripe Payouts to make this payment.');
            }
        }

        return $updatedOrderCount;
    }

    public function totalEarning($vendorOrderCollection) {
        $total = 0;
        foreach ($vendorOrderCollection as $order) {
            $payoutAmount = $this->vendorPayout->getPayoutAmount($order->getVendorId(), $order->getOrderId()) ?? 0;
            $total += $payoutAmount;
        }
        return $total;
    }

    public function vendorPayoutsProcess($payout, $payoutRef)
    {
        try {
            $currentPayoutTypeId = false;
            if ($this->isProcessPayoutForStripe($payout->getVendorId())) {
                //Process Manual payout for Vendor with Stripe Payout type
                $vendorPayoutType = $this->vendorPayoutTypeCollection->create()->addFieldToFilter('vendor_id', ['eq' => $payout->getVendorId()])->getFirstItem();
                $currentPayoutTypeId = $vendorPayoutType->getPayoutTypeId();
                /** temporarily update Vendor Payout Type to Manual */
                $vendorPayoutType->setPayoutTypeId($this->getPayoutTypeId());
                $vendorPayoutType->save();
                $updatedOrderCount = $this->vendorManualPayoutsProcess($payout, $payoutRef);
                /** Set Vendor Payout Type back to Stripe */
                $vendorPayoutType->setPayoutTypeId($currentPayoutTypeId);
                $vendorPayoutType->save();
            } else {
                //Process Manual payout for Vendor with Manual Payout type
                $updatedOrderCount = $this->vendorManualPayoutsProcess($payout, $payoutRef);
            }
        } catch (\Exception $e) {
            if ($currentPayoutTypeId !== false) {
                $vendorPayoutType->setPayoutTypeId($currentPayoutTypeId);
                $vendorPayoutType->save();
            }
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $updatedOrderCount;
    }
}
