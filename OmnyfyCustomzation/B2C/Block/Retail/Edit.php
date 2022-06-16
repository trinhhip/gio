<?php


namespace OmnyfyCustomzation\B2C\Block\Retail;


use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Directory\Block\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use OmnyfyCustomzation\B2C\Helper\Data as HelperData;
use OmnyfyCustomzation\Customer\Block\Widget\BusinessType;

class Edit extends \Magento\Customer\Block\Form\Edit
{
    /**
     * @var HelperData
     */
    public $helperData;

    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        HelperData $helperData,
        array $data = []
    )
    {
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }

    public function getCustomerCustomAttribute($attributeCode, $customer = null)
    {
        if (!$customer) {
            $customer = $this->getCustomer();
        }
        $customAttributes = $customer->getCustomAttributes();
        return isset($customAttributes[$attributeCode]) ? $customAttributes[$attributeCode]->getValue() : null;
    }

    public function getBusinessLocationHtml($customer)
    {
        $locationWidget = $this->getLayout()->createBlock(Data::class);
        return $locationWidget->getCountryHtmlSelect(
            $this->getCustomerCustomAttribute('business_location', $customer),
            'business_location',
            'business_location',
            'Business Location');
    }

    public function getBusinessTypeHtml($customer)
    {
        return $this->getLayout()
            ->createBlock(BusinessType::class)
            ->setBusinessType($this->getCustomerCustomAttribute('business_type', $customer))
            ->toHtml();
    }

    public function isRetailBuyer()
    {
        return $this->helperData->isRetailBuyer();
    }

}
