<?php
namespace Omnyfy\Stripe\Plugin\Model;

use Omnyfy\Mcm\Model\ResourceModel\PayoutType\CollectionFactory as PayoutTypeCollection;
use Omnyfy\Vendor\Model\VendorFactory;
use Omnyfy\VendorSignUp\Model\ResourceModel\VendorKyc\CollectionFactory as KycCollection;
use Omnyfy\VendorSignUp\Model\Source\KycStatus;

class VendorPayoutType
{
    protected $vendorFactory;

    protected $payoutTypeCollection;

    protected $kycCollection;

    public function __construct(
        VendorFactory $vendorFactory,
        PayoutTypeCollection $payoutTypeCollection,
        KycCollection $kycCollection
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->payoutTypeCollection = $payoutTypeCollection;
        $this->kycCollection = $kycCollection;
    }

    public function beforeSetData(\Omnyfy\Mcm\Model\VendorPayoutType $subject, $key, $value = null)
    {
        if ($subject->isObjectNew() && $key == 'payout_type_id' ) {
            $stripePayoutTypeId = $this->payoutTypeCollection->create()->addFieldToFilter('payout_type', ['eq' => "Stripe"])->getFirstItem()->getId();
            $vendor = $this->vendorFactory->create()->load($subject->getVendorId());
            $vendorKyc = $this->kycCollection->create()
                ->addFieldToFilter('vendor_id', ['eq' => $vendor->getId()]);
            if (empty($vendorKyc->getSize())) {
                return [$key, $value];
            }
            $kycStatus = $vendorKyc->getFirstItem()->getKycStatus();
            if (!empty($vendor->getStripeAccountCode()) && ($kycStatus == KycStatus::STATUS_APPROVED)) {
                $value = $stripePayoutTypeId;
            }
        }
        return [$key, $value];
    }
}
