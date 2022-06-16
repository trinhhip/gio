<?php

namespace OmnyfyCustomzation\BuyerApproval\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context as Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton as BaseGenericButton;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry as Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use OmnyfyCustomzation\BuyerApproval\Helper\Data;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class Approve
 *
 * @package OmnyfyCustomzation\BuyerApproval\Block\Adminhtml\Edit
 */
class GenericButton extends BaseGenericButton implements ButtonProviderInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Approve constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param AccountManagementInterface $customerAccountManagement
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AccountManagementInterface $customerAccountManagement,
        Data $helperData,
        \Magento\Framework\AuthorizationInterface $authorization
    )
    {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->helperData = $helperData;
        $this->authorization = $context->getAuthorization() ?: $authorization;

        parent::__construct($context, $registry);
    }

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
    }
}
