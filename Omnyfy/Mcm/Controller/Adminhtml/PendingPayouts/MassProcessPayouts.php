<?php

namespace Omnyfy\Mcm\Controller\Adminhtml\PendingPayouts;

use Magento\Backend\App\Action\Context;
use Omnyfy\Mcm\Api\VendorPayoutInterface;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayout\CollectionFactory as VendorPayoutCollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Omnyfy\Mcm\Model\ResourceModel\VendorOrder\CollectionFactory as VendorOrderCollectionFactory;
use Omnyfy\Mcm\Model\VendorPayoutHistoryFactory;
use Omnyfy\Mcm\Model\SequenceFactory;
use Omnyfy\Mcm\Model\VendorPayoutInvoiceFactory;
use Omnyfy\Mcm\Model\VendorPayoutInvoice\VendorPayoutInvoiceOrderFactory;
use Omnyfy\RebateCore\Model\Repository\TransactionRebateRepository;
use Omnyfy\Mcm\Model\Config\Source\PayoutBasisType;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\CollectionFactory as VendorPayoutTypeCollection;
use Omnyfy\Mcm\Model\PayoutType;

/**
 * Class MassProcessPayouts
 */
class MassProcessPayouts extends \Omnyfy\Mcm\Controller\Adminhtml\AbstractAction
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter $filter
     */
    protected $filter;

    protected $vendorPayoutCollectionFactory;

    protected $vendorOrderCollectionFactory;

    protected $vendorPayoutHistoryFactory;

    protected $sequenceFactory;

    protected $vendorPayoutInvoiceFactory;

    protected $vendorPayoutInvoiceOrderFactory;

    protected $_config;

    protected $mcmHelper;

    protected $resourceConnection;

    protected $orderRepository;

    protected $payoutHelper;

    protected $emptyMessage = 'No payout has been processed as the selected vendors do not have any Pending Payout amounts';

    protected $transactionRebateRepository;

    protected $vendorOrderResourceFactory;

    protected $orderItemRepository;

    protected $vendorRepository;


    /**
     * @var Omnyfy\Mcm\Model\ResourceModel\FeesManagement
     */
    protected $feesManagementResource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var VendorPayoutTypeCollection
     */
    protected $vendorPayoutTypeCollection;

    /**
     * MassProcessPayouts constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param Filter $filter
     * @param VendorPayoutCollectionFactory $vendorPayoutCollectionFactory
     * @param VendorOrderCollectionFactory $vendorOrderCollectionFactory
     * @param VendorPayoutHistoryFactory $vendorPayoutHistoryFactory
     * @param SequenceFactory $sequenceFactory
     * @param VendorPayoutInvoiceFactory $vendorPayoutInvoiceFactory
     * @param VendorPayoutInvoiceOrderFactory $vendorPayoutInvoiceOrderFactory
     */
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
        TransactionRebateRepository $transactionRebateRepository,
        \Omnyfy\Mcm\Model\ResourceModel\VendorOrderFactory $vendorOrderResourceFactory,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        VendorPayoutTypeCollection $vendorPayoutTypeCollection,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource
    ) {
        $this->filter = $filter;
        $this->vendorPayoutCollectionFactory = $vendorPayoutCollectionFactory;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->vendorPayoutHistoryFactory = $vendorPayoutHistoryFactory;
        $this->sequenceFactory = $sequenceFactory;
        $this->vendorPayoutInvoiceFactory = $vendorPayoutInvoiceFactory;
        $this->vendorPayoutInvoiceOrderFactory = $vendorPayoutInvoiceOrderFactory;
        $this->_config = $config;
        $this->mcmHelper = $mcmHelper;
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->payoutHelper = $payoutHelper;
        $this->transactionRebateRepository = $transactionRebateRepository;
        $this->vendorOrderResourceFactory = $vendorOrderResourceFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->vendorRepository = $vendorRepository;
        $this->scopeConfig = $scopeConfig;
        $this->vendorPayoutTypeCollection = $vendorPayoutTypeCollection;
        $this->feesManagementResource = $feesManagementResource;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->vendorPayoutCollectionFactory->create());

            foreach ($collection as $payout) {
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
            $size = $vendorOrderCollection->getSize();
            if ($size > 150) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The max number of orders that can be processed per Payout is 150')
                );
            }
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

    public function vendorPayoutsProcess($payout, $payoutRef)
    {
        $vendorTotalPayoutAmount = 0.00;
        $vendorPayoutAmountInvoice = 0.00;
        $vendorPayoutInvoiceData = [
            'payout_ref' => $payoutRef,
            'increment_id' => $this->getRef('invoice_ref'),
            'vendor_id' => $payout->getVendorId(),
            'orders_total_incl_tax' => 0.00,
            'orders_total_tax' => 0.00,
            'fees_total_incl_tax' => 0.00,
            'fees_total_tax' => 0.00,
            'shipping_total_for_order' => 0.00,
            'disbursement_fee' => 0.00,
            'category_commission' => 0.00
        ];
        $vendorId = $payout->getVendorId();
        $vendorName = $payout->getVendorName();
        $payoutId = $payout->getPayoutId();
        $accountRef = $payout->getAccountRef();
        $vendorOrderCollection = $this->getVendorOrderCollection($vendorId);
        $updatedOrderCount = 0;

        if (!empty($vendorOrderCollection)) {
            $vendorOrderIds = [];
            $transactionIds = [];

            foreach ($vendorOrderCollection as $vendorOrder) {
                $updatedOrderCount++;
                $vendorOrder->setPayoutStatus(3); //Payout status 3 => In progress
                $vendorOrder->save();
                //manage payout history
                // If there is a record in omnyfy_mcm_shipping_calculation use that for order
                $doesShippingMcmCalculationExist = $this->payoutHelper->doesShippingCalculationExist($vendorOrder);
                if ($doesShippingMcmCalculationExist) {
                    $orderTotalIncludingShippingPayout = $this->payoutHelper->getOrderPayoutShippingAmount($vendorOrder);
                    $vendorOrderTotalIncTax = ($vendorOrder->getBaseGrandTotal() + ($vendorOrder->getShippingInclTax() - $vendorOrder->getShippingDiscountAmount()));
                    $vendorPayoutInvoiceData['shipping_total_for_order'] = $vendorOrder->getShippingInclTax() - $vendorOrder->getShippingDiscountAmount();
                } else {
                    // Check if we need to retain shipping as marketplace + fallback for existing
                    $vendorOrderTotalIncTax = ($vendorOrder->getBaseGrandTotal() + ($vendorOrder->getShippingInclTax() - $vendorOrder->getShippingDiscountAmount()));
                }

                if (!$this->mcmHelper->manageShippingFees()) {
                    $vendorPayoutInvoiceData['shipping_total_for_order'] = $vendorOrder->getShippingInclTax() - $vendorOrder->getShippingDiscountAmount();
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

                // @TODO - check this logic, it seems to not be getting hit on payout amount which is effecting history
                $rebateOrder = $this->transactionRebateRepository->getMaturedVendorRebateTransactions($vendorOrder->getVendorId(), $vendorOrder->getOrderId());
                if (isset($rebateOrder['rebate_transactions'])) {
                    foreach ($rebateOrder['rebate_transactions'] as $transaction) {
                        $transcationsId = $transaction['entity_id'];
                        $this->transactionRebateRepository->startProcess($transcationsId);
                        $payoutAmount -= $transaction['rebate_total_amount'];
                        $transactionIds[$vendorOrder->getId()][] = $transcationsId;
                    }
                }

                // If MCM is set not to manage shipping fees, if ship by type is also disabled, pay out to vendor
                if ($this->mcmHelper->manageShippingFees()) {
                    $payoutAmount -= $vendorOrder->getShippingInclTax();
                }

                $accountRef = $payout->getAccountRef();

                $vendorPayoutHistoryData = [
                    'payout_id' => $payoutId,
                    'vendor_id' => $vendorId,
                    'vendor_order_id' => $vendorOrder->getId(),
                    'payout_ref' => $payoutRef,
                    'payout_amount' => $payoutAmount
                ];

                $vendorPayoutType = $this->vendorPayoutTypeCollection->create();
                $resource = $vendorPayoutType->getResource();
                $vendorPayoutType->addFieldToFilter('vendor_id', ['eq' => $payout->getVendorId()])
                    ->join(
                        ['payout_type' => $resource->getTable('omnyfy_mcm_payout_type')],
                        'main_table.payout_type_id = payout_type.id',
                        ['payout_type']
                    );
                $payoutType = $vendorPayoutType->getFirstItem()->getPayoutType();

                // If it is a manual payout with no account id, ensure we don't set vendor history status to pending
                if ($payoutType == PayoutType::DEFAULT_TYPE) {
                    $vendorPayoutHistoryData['status'] = 1;
                    $vendorPayoutHistoryData['payout_type_id'] = $this->mcmHelper->getPayoutTypeId($payoutType);
                } else {
                    $vendorPayoutHistoryData['status'] = 2;
                }

                $vendorPayoutInvoiceData['category_commission'] += $this->feesManagementResource->getVendorCategoryFee($vendorOrder->getOrderId(), $vendorId);

                $this->saveVendorPayoutHistory($vendorPayoutHistoryData);
                $vendorOrderIds[] = $vendorOrder->getId();
                $vendorTotalPayoutAmount += $payoutAmount;
                $vendorPayoutAmountInvoice += $payoutAmount;

                $vendorPayoutInvoiceData['orders_total_incl_tax'] += $vendorOrderTotalIncTax;
                $vendorPayoutInvoiceData['orders_total_tax'] += $vendorOrder->getBaseTaxAmount();
                $vendorPayoutInvoiceData['fees_total_incl_tax'] += $vendorOrderFeeTotalIncTax;
                $vendorPayoutInvoiceData['fees_total_tax'] += $vendorOrderTotalFeeTax;

                if (!$this->scopeConfig->getValue('tax/calculation/shipping_includes_tax')) {
                    $vendorPayoutInvoiceData['orders_total_tax'] += $vendorOrder->getShippingTax();
                }

                $vendorPayoutInvoiceData['disbursement_fee'] += $vendorOrder->getDisbursementFee();
            }


            /*
             * Integrate with assembly pay for send payout
             */
            $eventData = [
                'payout_ref' => $payoutRef,
                'account_ref' => $accountRef,
                'amount' => $this->payoutHelper->totalEarning($vendorOrderCollection),
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

        return $updatedOrderCount;
    }


    public function rebateOrder($vendorId, $orderId)
    {
        return $this->transactionRebateRepository->getPerOrderSettlementTransactions($vendorId, $orderId);
    }

    public function getOrderShippingFee($order)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('omnyfy_vendor_quote_shipping'); //gives table name with prefix

        $magentoOrder = $this->orderRepository->get($order->getOrderId());
        $quoteId = $magentoOrder->getQuoteId();

        //Select Data from table
        $sql = 'SELECT amount FROM ' . $tableName . ' WHERE `quote_id` =  ' . $quoteId . ' AND vendor_id = ' . $order->getVendorId();
        $result = $connection->fetchOne($sql); // gives associated array, table fields as key in array.

        return $result;
    }

    /**
     *
     * @param type $vendorId
     * @return type
     */
    protected function getVendorOrderCollection($vendorId)
    {
        $vendorOrderCollection = $this->vendorOrderCollectionFactory->create();
        $vendorOrderCollection = $vendorOrderCollection->addFieldToFilter('vendor_id', $vendorId)
                ->addFieldToFilter('payout_status', 0)
                ->addFieldToFilter('payout_action', 1);
        return $vendorOrderCollection;
    }

    /**
     * @param type $vendorPayoutHistoryData
     */
    protected function saveVendorPayoutHistory($vendorPayoutHistoryData)
    {
        $vendorPayoutHistoryModel = $this->vendorPayoutHistoryFactory->create();
        $vendorPayoutHistoryModel->setData($vendorPayoutHistoryData);
        $vendorPayoutHistoryModel->save();
    }

    /**
     *
     * @param type $vendorPayoutInvoiceData
     * @param type $vendorOrderCollection
     */
    protected function saveVendorPayoutInvoice($vendorPayoutInvoiceData, $vendorOrderCollection)
    {
        $vendorPayoutInvoiceModel = $this->vendorPayoutInvoiceFactory->create();
        $vendorPayoutInvoiceModel->setData($vendorPayoutInvoiceData);
        $vendorPayoutInvoiceModel->save();

        if (!empty($vendorOrderCollection)) {
            foreach ($vendorOrderCollection as $vendorOrder) {
                // If there is a record in omnyfy_mcm_shipping_calculation use that for order
                $doesShippingMcmCalculationExist = $this->payoutHelper->doesShippingCalculationExist($vendorOrder);
                if ($doesShippingMcmCalculationExist) {
                    $orderTotalIncludingShippingPayout = $this->payoutHelper->getOrderPayoutShippingAmount($vendorOrder);
                    $vendorOrderTotalIncTax = ($vendorOrder->getBaseGrandTotal() + $orderTotalIncludingShippingPayout - $vendorOrder->getShippingDiscountAmount());
                } else {
                    // fallback for existing
                    $vendorOrderTotalIncTax = ($vendorOrder->getBaseGrandTotal() + $vendorOrder->getBaseShippingAmount() - $vendorOrder->getShippingDiscountAmount());
                }

                $vendorOrderTotalFees = $vendorOrder->getTotalCategoryFee() + $vendorOrder->getTotalSellerFee() + $vendorOrder->getDisbursementFee();
                $vendorOrderTotalFeeTax = $vendorOrder->getTotalCategoryFeeTax() + $vendorOrder->getTotalSellerFeeTax() + $vendorOrder->getDisbursementFeeTax();
                $vendorOrderFeeTotalIncTax = $vendorOrderTotalFees + $vendorOrderTotalFeeTax;
                $vendorPayoutInvoiceOrderData = [
                    'invoice_id' => $vendorPayoutInvoiceModel->getId(),
                    'vendor_id' => $vendorPayoutInvoiceData['vendor_id'],
                    'order_id' => $vendorOrder->getOrderId(),
                    'order_increment_id' => $vendorOrder->getOrderIncrementId(),
                    'order_total_incl_tax' => $vendorOrderTotalIncTax,
                    'order_total_tax' => $vendorOrder->getBaseTaxAmount(),
                    'fees_total_incl_tax' => $vendorOrderFeeTotalIncTax,
                    'fees_total_tax' => $vendorOrderTotalFeeTax,
                    'shipping_total_for_order' => 0.00
                ];

                if (!$this->scopeConfig->getValue('tax/calculation/shipping_includes_tax')) {
                    $vendorPayoutInvoiceOrderData['order_total_incl_tax'] += $vendorOrder->getShippingTax();
                }

                if (!$this->mcmHelper->manageShippingFees()) {
                    $vendorPayoutInvoiceOrderData['shipping_total_for_order'] = $vendorOrder->getShippingInclTax() - $vendorOrder->getShippingDiscountAmount();
                }

                $this->saveVendorPayoutInvoiceOrderData($vendorPayoutInvoiceOrderData);
            }
        }
    }

    /**
     * @param array $vendorPayoutInvoiceOrderData
     * @throws \Exception
     */
    protected function saveVendorPayoutInvoiceOrderData($vendorPayoutInvoiceOrderData)
    {
        $vendorPayoutInvoiceOrderModel = $this->vendorPayoutInvoiceOrderFactory->create();
        $vendorPayoutInvoiceOrderModel->setData($vendorPayoutInvoiceOrderData);
        $vendorPayoutInvoiceOrderModel->save();
    }

    /**
     *
     * @param string $type payout_ref | invoice_ref
     * @return string | boolean
     */
    public function getRef($type = 'payout_ref')
    {
        $sequenceObj = $this->loadRefSequence($type);
        if (!empty($sequenceObj->getData())) {
            $currentValue = $sequenceObj->getLastValue() + 1;
            return $sequenceObj->getPrefix() . str_pad($currentValue, 12, '0', STR_PAD_LEFT);
        } else {
            return false;
        }
    }

    /**
     *
     * @param type $lastRef
     * @param type $type payout_ref | invoice_ref
     */
    public function setLastRef($lastRef, $type = 'payout_ref')
    {
        $sequenceObj = $this->loadRefSequence($type);
        $lastValue = ltrim(str_replace($sequenceObj->getPrefix(), '', $lastRef), '0');
        $sequenceObj->setLastValue($lastValue);
        $sequenceObj->save();
    }

    /**
     *
     * @param type $refType payout_ref | invoice_ref
     * @return type
     */
    public function loadRefSequence($refType = 'payout_ref')
    {
        $sequenceFactory = $this->sequenceFactory->create();
        return $sequenceFactory->load($refType, 'type');
    }

    public function getTotalOrderByWholesaleVendor($orderId, $vendorId)
    {
        $items = $this->vendorOrderResourceFactory->create()->getOrderItems($orderId, $vendorId);
        $total = 0;
        foreach ($items as $item) {
            $itemCollection = $this->orderItemRepository->get($item['order_item_id']);
            $cost = $itemCollection->getBaseCost();
            $qty = $itemCollection->getQtyOrdered() - $itemCollection->getQtyRefunded();
            $total += $cost * $qty;
        }



        return $total;
    }

    public function getVendorPayoutBasisType($vendorId)
    {
        $vendor = $this->vendorRepository->getById($vendorId);
        return $vendor->getPayoutBasisType();
    }
}
