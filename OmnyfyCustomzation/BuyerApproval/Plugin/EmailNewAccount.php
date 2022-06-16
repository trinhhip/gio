<?php
namespace OmnyfyCustomzation\BuyerApproval\Plugin;

use Closure;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotification;
use OmnyfyCustomzation\BuyerApproval\Helper\Data as HelperData;

/**
 * Class EmailNewAccount
 *
 * @package OmnyfyCustomzation\BuyerApproval\Plugin
 */
class EmailNewAccount
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * EmailNewAccount constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param EmailNotification $subject
     * @param Closure $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param null $sendemailStoreId
     *
     * @return                   mixed|null
     * @SuppressWarnings(Unused)
     */
    public function aroundNewAccount(
        \OmnyfyCustomzation\Customer\Model\EmailNotification $subject,
        Closure $proceed,
        CustomerInterface $customer,
        $type = EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        if (!$this->helperData->isEnabled()
            || (!$this->helperData->hasCustomerEdit() && $this->helperData->isAdmin())
            || $customer->getConfirmation()
        ) {
            return $proceed($customer, $type, $backUrl, $storeId, $sendemailStoreId);
        }

        return null;
    }
}
