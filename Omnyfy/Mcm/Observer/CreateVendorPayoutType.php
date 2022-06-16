<?php
namespace Omnyfy\Mcm\Observer;

use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory as VendorCollection;
use Omnyfy\Mcm\Model\VendorPayoutTypeFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\CollectionFactory as VendorPayoutTypeCollection;
use Omnyfy\Mcm\Model\ResourceModel\PayoutType\CollectionFactory as PayoutTypeCollection;
use Omnyfy\Mcm\Model\PayoutType;
use Omnyfy\VendorSubscription\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

class CreateVendorPayoutType implements \Magento\Framework\Event\ObserverInterface
{
    protected $vendorCollection;
    protected $vendorPayoutTypeFactory;
    protected $vendorPayoutTypeCollection;
    protected $payoutTypeResource;
    protected $payoutTypeCollection;
    protected $helper;

    public function __construct(
        VendorCollection $vendorCollection,
        VendorPayoutTypeFactory $vendorPayoutTypeFactory,
        VendorPayoutTypeCollection $vendorPayoutTypeCollection,
        PayoutTypeCollection $payoutTypeCollection,
        Data $helper
    ) {
        $this->vendorCollection = $vendorCollection;
        $this->vendorPayoutTypeFactory = $vendorPayoutTypeFactory;
        $this->vendorPayoutTypeCollection = $vendorPayoutTypeCollection;
        $this->payoutTypeCollection = $payoutTypeCollection;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendor = $observer->getData('vendor');
        $signUp = $observer->getData('sign_up');

        $extraInfo = $signUp->getExtraInfoAsArray();

        if (empty($extraInfo) || !is_array($extraInfo) ) {
            throw new LocalizedException(__('Something wrong while trying to approve.'));
        }

        if (!isset($extraInfo['plan_role_id'])) {
            throw new LocalizedException(__('Cannot approve without plan and role selected'));
        }

        list($planId, $roleId) = explode('_', $extraInfo['plan_role_id']);

        $plan = $this->helper->loadPlanById($planId);
        if (empty($plan)) {
            throw new LocalizedException(__('Cannot approve with wrong plan %1', $planId));
        }

        $role = $this->helper->getRoleById($roleId);
        if (empty($roleId) || empty($role->getId())) {
            throw new LocalizedException(__('Cannot approve with wrong role %1', $roleId));
        }

        if (!$plan->getIsFree() && !array_key_exists('card_token', $extraInfo)) {
            throw new LocalizedException(__('Required information for payment not provided'));
        }

        $defaultTypeId = $this->payoutTypeCollection->create()->addFieldToFilter('payout_type', ['eq' => PayoutType::DEFAULT_TYPE])->getFirstItem()->getId();
        if (empty($defaultTypeId)) {
            return;
        }

        $vendorPayoutType = $this->vendorPayoutTypeFactory->create();
        $vendorPayoutType->setData('vendor_id', $vendor->getId());
        $vendorPayoutType->setData('payout_type_id', $defaultTypeId);
        $vendorPayoutType->save();
    }
}
