<?php
namespace Omnyfy\Mcm\Controller\Adminhtml\PayoutType;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\CollectionFactory;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory as VendorCollectionFactory;
use Omnyfy\Vendor\Model\VendorFactory;
use Omnyfy\Mcm\Model\PayoutTypeFactory;

class MassUpdate extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Omnyfy_Mcm::select_payout_type';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    protected $vendorColectionFactory;

    protected $payoutTypeFactory;

    protected $vendorFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        VendorCollectionFactory $vendorColectionFactory,
        PayoutTypeFactory $payoutTypeFactory,
        VendorFactory $vendorFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->vendorColectionFactory = $vendorColectionFactory;
        $this->payoutTypeFactory = $payoutTypeFactory;
        $this->vendorFactory = $vendorFactory;

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
        $selectedIds = $this->getRequest()->getParam(Filter::SELECTED_PARAM);
        $collection = $this->collectionFactory->create();
        if (!empty($selectedIds)) {
            $collection->addFieldToFilter('id', ['in' => $selectedIds]);
        }
        $payoutTypeId = $this->getRequest()->getParam('payout_type_id');
        $payoutTypeToUpdate = $this->payoutTypeFactory->create()->load($payoutTypeId);

        $duplicatePayoutTypeVendorIds = [];
        $notStripeVendorIds = [];

        foreach ($collection->getItems() as $item) {
            $vendorId = $item->getVendorId();
            $vendor = $this->vendorFactory->create()->load($vendorId);
            if ($item->getData('payout_type_id') == $payoutTypeId) {
                $duplicatePayoutTypeVendorIds[] = $vendorId;
            } elseif ($payoutTypeToUpdate->getPayoutType() == "Stripe" && empty($vendor->getStripeAccountCode())) {
                $notStripeVendorIds[] = $vendorId;
            }
        }

        if (count($duplicatePayoutTypeVendorIds) > 0) {
            $vendors = $this->vendorColectionFactory->create()
                ->addFieldToSelect('name')
                ->addFieldToFilter('entity_id', ['in' => $duplicatePayoutTypeVendorIds]);
            $this->messageManager->addErrorMessage(
                __("Vendor(s) \"%1\" already have %2 Payout Type",
                    implode(', ', $vendors->getColumnValues('name')),
                    $payoutTypeToUpdate->getPayoutType())
            );
        }

        if (count($notStripeVendorIds) > 0) {
            $vendors = $this->vendorColectionFactory->create()
                ->addFieldToSelect('name')
                ->addFieldToFilter('entity_id', ['in' => $notStripeVendorIds]);
            $this->messageManager->addErrorMessage(
                __("Vendor(s) \"%1\" doesn't have Stripe Account, cannot set Payout Type to Stripe",
                    implode(', ', $vendors->getColumnValues('name'))
                )
            );
        }

        $updatedItemCount = 0;
        foreach ($collection->getItems() as $item) {
            if (in_array($item->getVendorId(), $duplicatePayoutTypeVendorIds) || in_array($item->getVendorId(), $notStripeVendorIds)) {
                continue;
            }
            $item->setData('payout_type_id', $payoutTypeId);
            $item->save();
            $updatedItemCount++;
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 vendor(s) have been updated.', $updatedItemCount));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
