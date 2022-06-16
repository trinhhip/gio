<?php
namespace OmnyfyCustomzation\BuyerApproval\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use OmnyfyCustomzation\BuyerApproval\Helper\Data as HelperData;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\TypeAction;

/**
 * Class CustomerSaveAfter
 *
 * @package OmnyfyCustomzation\BuyerApproval\Observer
 */
class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * CustomerSaveAfter constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
            return;
        }

        $customerId = $customer->getId();
        $hasCustomerEdit = $this->helperData->hasCustomerEdit();
        // case create customer in adminhtml
        if (!$hasCustomerEdit && $customerId) {
                $actionRegister = false;
                $this->helperData->setApprovePendingById($customerId, $actionRegister);
                $this->helperData->emailNotifyAdmin($customer);

        }
    }
}
