<?php
namespace OmnyfyCustomzation\BuyerApproval\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use OmnyfyCustomzation\BuyerApproval\Helper\Data;

/**
 * Class View
 *
 * @package OmnyfyCustomzation\BuyerApproval\Block\Adminhtml\Edit\Tab
 */
class View extends Template
{
    /**
     * @var Data
     */
    public $helperData;

    /**
     * View constructor.
     *
     * @param Template\Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getApprovedLabel()
    {
        $customerId = $this->getRequest()->getParam('id');
        $value = $this->helperData->getIsApproved($customerId);

        return $this->helperData->getApprovalLabel($value);
    }

    /**
     * @return int|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isEnabled()
    {
        $customerId = $this->getRequest()->getParam('id');
        $customer = $this->helperData->getCustomerById($customerId);

        return $this->helperData->isEnabledForWebsite($customer->getWebsiteId());
    }
}
