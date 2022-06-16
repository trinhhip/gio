<?php

namespace Omnyfy\VendorAuth\Observer\Backend\Controller;

use Omnyfy\Vendor\Model\Resource\Vendor as VendorResource;

class CustomerSave implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;


    /**
     * @var \Omnyfy\VendorAuth\Model\Vendor
     */
    protected $_vendorAuthVendor;

    protected $vendorAuthVendorHelper;
    protected $vendorResource;

    /**
     * RestrictWebsite constructor.
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Omnyfy\VendorAuth\Helper\Config $vendorAuthVendorHelper,
        VendorResource $vendorResource
    )
    {
        $this->session = $session;
        $this->vendorAuthVendorHelper = $vendorAuthVendorHelper;
        $this->vendorResource = $vendorResource;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    )
    {
        /**
         * @var \Magento\Customer\Model\Customer $customer
         */
        $customer = $observer->getCustomer();

        //check if it is a vendor user
        $vendorSession = $this->session->getVendorInfo();

        if (empty($vendorSession))
            return;

        //check vendor can create new customer
        if (!$this->vendorAuthVendorHelper->isVendorCreateCustomer()) {
            return;
        }

        $customerVendorRelation[] = ['customer_id' => $customer->getId(), 'vendor_id' => $vendorSession['vendor_id']];
        $this->vendorResource->saveCustomerRelation($customerVendorRelation);

    }

}
