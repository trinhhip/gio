<?php


namespace OmnyfyCustomzation\B2C\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use OmnyfyCustomzation\B2C\Helper\BuyerAccount as BuyerAccountHelper;
use OmnyfyCustomzation\B2C\Helper\Data;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;

class UpdateStatus implements ObserverInterface
{
    /**
     * @var BuyerAccountHelper
     */
    private BuyerAccountHelper $buyerAccountHelper;
    private Data $helperData;

    public function __construct(
        BuyerAccountHelper $buyerAccountHelper,
        Data $helperData
    )
    {
        $this->buyerAccountHelper = $buyerAccountHelper;
        $this->helperData = $helperData;
    }

    public function execute(Observer $observer)
    {
        $customer = $observer->getCustomer();
        if ($customer->getGroupId() == $this->helperData->getTradeCustomerGroup()) {
            $this->buyerAccountHelper->requestToTrade($customer->getEmail(), AttributeOptions::PENDING);
        }
    }
}
