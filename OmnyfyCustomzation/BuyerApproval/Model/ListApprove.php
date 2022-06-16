<?php
namespace OmnyfyCustomzation\BuyerApproval\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use OmnyfyCustomzation\BuyerApproval\Api\ListApproveInterface;
use OmnyfyCustomzation\BuyerApproval\Helper\Data;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\TypeAction;

/**
 * Class ListApprove
 *
 * @package OmnyfyCustomzation\BuyerApproval\Model
 */
class ListApprove implements ListApproveInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * ListApprove constructor.
     *
     * @param Data $helperData
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Data $helperData,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->helperData = $helperData;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function approveCustomer($email)
    {
        try {
            $customer = $this->customerRepository->get($email);
            if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
                throw new LocalizedException(__('Module is not enabled for the website of this customer'));
            }

            $customerId = $customer->getId();
            if ($this->helperData->getIsApproved($customerId) != AttributeOptions::APPROVED) {
                $this->helperData->approvalCustomerById($customerId, TypeAction::API);
            }
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function notApproveCustomer($email)
    {
        try {
            $customer = $this->customerRepository->get($email);
            if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
                throw new LocalizedException(__('Module is not enabled for the website of this customer'));
            }

            $customerId = $customer->getId();
            if ($this->helperData->getIsApproved($customerId) != AttributeOptions::NOTAPPROVE) {
                $this->helperData->notApprovalCustomerById($customerId);
            }
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return true;
    }
}
