<?php

namespace Omnyfy\Enquiry\Plugin;

use Magento\Customer\Helper\Session\CurrentCustomer;

class CustomerData
{
    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        CurrentCustomer $currentCustomer,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->currentCustomer = $currentCustomer;
        $this->customerFactory = $customerFactory;
    }

    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        if ($this->currentCustomer->getCustomerId()) {
            $customer = $this->currentCustomer->getCustomer();
            $result['email'] = $customer->getEmail();
            $result['lastname'] = $customer->getLastname();
            if (isset($customer->getAddresses()[0])) {
                $result['phone'] = $customer->getAddresses()[0]->getTelephone();
                $result['company'] = $customer->getAddresses()[0]->getCompany();
            }
        }
        return $result;
    }
}