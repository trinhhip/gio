<?php
namespace Omnyfy\Stripe\Controller\Adminhtml\PendingPayouts;

use Magento\Ui\Component\MassAction\Filter;
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

class MassProcessStripePayouts extends \Omnyfy\Mcm\Controller\Adminhtml\PendingPayouts\MassProcessPayouts
{
    const PAYOUT_TYPE = "Stripe";

    protected $payoutTypeCollection;

    protected $vendorPayoutTypeCollection;

    protected $vendorPayoutCollection;

    protected $emptyMessage = "No Payouts Processed as none of the payouts selected have been processed by Stripe. If the order payments have been made by non-Stripe payment methods, please use “Process Manual Payouts” to mark these orders as paid out";

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement $feesManagementResource
    ) {
        $this->payoutTypeCollection = $payoutTypeCollection;
        $this->vendorPayoutTypeCollection = $vendorPayoutTypeCollection;
        $this->vendorPayoutCollection = $vendorPayoutCollection;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger, $filter, $vendorPayoutCollectionFactory, $vendorOrderCollectionFactory, $vendorPayoutHistoryFactory, $sequenceFactory, $vendorPayoutInvoiceFactory, $vendorPayoutInvoiceOrderFactory, $config, $mcmHelper, $resourceConnection, $orderRepository, $payoutHelper, $transactionRebateRepository, $vendorOrderResourceFactory, $orderItemRepository, $vendorRepository, $scopeConfig, $vendorPayoutTypeCollection, $feesManagementResource);
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

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->vendorPayoutCollectionFactory->create());
            $vendorOrderCollection = $this->vendorOrderCollectionFactory->create();
            $vendorOrderCollection = $vendorOrderCollection->addFieldToFilter('vendor_id', ['in' => $collection->getColumnValues('vendor_id')])
                ->addFieldToFilter('payout_status', ['in' => [0,3]])
                ->addFieldToFilter('payout_action', 1);
            $size = $vendorOrderCollection->getSize();
            if ($size > 50) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The max number of orders that can be processed per Payout is 50')
                );
            }
            if ($vendorOrderCollection->getSize() > 0) {
                foreach ($collection as $payout) {
                    if ($this->getVendorPayoutType($payout->getVendorId()) != $this->getPayoutTypeId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot process payout. Vendor with ID %1 doesn\'t have Stripe payout type', $payout->getVendorId())
                        );
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('omnyfy_mcm/pendingpayouts/index');
            return $resultRedirect;
        }

        return parent::execute();
    }

    public function getVendorOrderCollection($vendorId)
    {
        $vendorOrderCollection = parent::getVendorOrderCollection($vendorId);
        $stripeMethods = ['stripebacs', 'stripe_payments'];
        $vendorOrderCollection->getSelect()
            ->join(
                ['payment' => $vendorOrderCollection->getTable('sales_order_payment')],
                'payment.parent_id = main_table.order_id',
                ['method']
            )->where('method IN (?)', $stripeMethods);

        return $vendorOrderCollection;
    }
}
