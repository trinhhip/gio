<?php
namespace Omnyfy\ManualPayout\Controller\Adminhtml\Kyc;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Omnyfy\Mcm\Model\PayoutType;
use Omnyfy\Mcm\Model\ResourceModel\PayoutType\CollectionFactory as PayoutTypeCollection;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\CollectionFactory;
use Magento\Framework\Event\ManagerInterface;
use Omnyfy\VendorSignUp\Model\Source\KycStatus;
use Magento\Framework\App\ResourceConnection;
use Omnyfy\VendorSignUp\Model\ResourceModel\VendorKyc\CollectionFactory as VendorKycCollection;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory as VendorCollection;

class MassApprove extends \Magento\Backend\App\Action
{
    const MANUAL_TYPE = "manual";
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Omnyfy_Mcm::select_payout_type';

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resoure;

    /**
     * @var PayoutTypeCollection
     */
    protected $payoutTypeCollection;

    /**
     * @var VendorKycCollection
     */
    protected $vendorKycCollection;

    /**
     * @var VendorCollection
     */
    protected $vendorCollection;

    /**
     * MassApprove constructor.
     * @param Context $context
     * @param ManagerInterface $eventManager
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resoure
     * @param PayoutTypeCollection $payoutTypeCollection
     * @param VendorKycCollection $vendorKycCollection
     * @param VendorCollection $vendorCollection
     */
    public function __construct(
        Context $context,
        ManagerInterface $eventManager,
        CollectionFactory $collectionFactory,
        ResourceConnection $resoure,
        PayoutTypeCollection $payoutTypeCollection,
        VendorKycCollection $vendorKycCollection,
        VendorCollection $vendorCollection
    ) {
        $this->eventManager = $eventManager;
        $this->collectionFactory = $collectionFactory;
        $this->resoure = $resoure;
        $this->connection = $resoure->getConnection();
        $this->payoutTypeCollection = $payoutTypeCollection;
        $this->vendorKycCollection = $vendorKycCollection;
        $this->vendorCollection = $vendorCollection;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\NotFoundException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $selectedIds = $this->getRequest()->getParam(Filter::SELECTED_PARAM);
            $collection = $this->collectionFactory->create();
            if (!empty($selectedIds)) {
                $collection->addFieldToFilter('id', ['in' => $selectedIds]);
            }
            $defaultTypeId = $this->payoutTypeCollection->create()->addFieldToFilter('payout_type', ['eq' => PayoutType::DEFAULT_TYPE])->getFirstItem()->getId();
            $approvedRows = 0;
            $approvedVendorIds = [];
            $countVendorNotDefaultType = 0;
            foreach ($collection->getItems() as $item) {
                $kycCollection = $this->vendorKycCollection->create();
                $kycCollection->addFieldToFilter('vendor_id', ['eq' => $item->getVendorId()]);
                $kycCollection->addFieldToFilter('kyc_status', ['eq' => KycStatus::STATUS_APPROVED]);
                if ($kycCollection->getSize() > 0) {
                    $approvedVendorIds[] = $item->getVendorId();
                    continue;
                }
                if ($item->getPayoutTypeId() != $defaultTypeId) {
                    $countVendorNotDefaultType++;
                    continue;
                }
                $this->eventManager->dispatch(
                    'omnyfy_vendorsignup_kyc_status_update',
                    [
                        'vendor_id' => $item->getVendorId(),
                        'status' => KycStatus::STATUS_APPROVED,
                        'account_ref' => self::MANUAL_TYPE
                    ]
                );
                $this->connection->update(
                    $this->resoure->getTableName('omnyfy_mcm_vendor_payout'),
                    [
                        'ewallet_id' => self::MANUAL_TYPE,
                        'account_ref' => self::MANUAL_TYPE,
                        'third_party_account_id' => self::MANUAL_TYPE
                    ],
                    ['vendor_id = ?' => $item->getVendorId()]
                );
                $approvedRows++;

            }

            if ($countVendorNotDefaultType > 0) {
                $this->messageManager->addWarningMessage(__("You are unable to manually set the KYC status of a Vendor using Stripe Account for payouts. Please check the KYC status of the Vendor in their profile. KYC status can only be set for Manual Payout Vendors"));
            }

            $approvedVendors = $this->vendorCollection->create()
                ->addFieldToSelect('name')
                ->addFieldToFilter('entity_id', ['in' => $approvedVendorIds]);

            if ($approvedVendors->getSize() > 0) {
                $vendorNames = implode(', ', $approvedVendors->getColumnValues('name'));
                $this->messageManager->addWarningMessage(__("Vendor(s) \"%1\" already have an Approved KYC status", $vendorNames));
            }

            if ($approvedRows > 0) {
                $this->messageManager->addSuccessMessage(__('A total of %1 vendor(s) have been approved.', $approvedRows));
            } else {
                $this->messageManager->addWarningMessage(__("There are no vendors to approve"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("Can't approve KYC status: %1", $e->getMessage()));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('omnyfy_mcm/payouttype/index');
    }
}
