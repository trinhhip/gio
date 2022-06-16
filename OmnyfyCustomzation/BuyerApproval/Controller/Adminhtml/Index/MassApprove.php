<?php

namespace OmnyfyCustomzation\BuyerApproval\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use OmnyfyCustomzation\BuyerApproval\Helper\Data;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\TypeAction;

/**
 * Class MassApprove
 *
 * @package OmnyfyCustomzation\BuyerApproval\Controller\Adminhtml\Index
 */
class MassApprove extends AbstractMassAction
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * MassApprove constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Data $helperData
    )
    {
        $this->helperData = $helperData;

        parent::__construct($context, $filter, $collectionFactory);
    }

    /**
     * @param AbstractCollection $collection
     *
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws Exception
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customersUpdated = 0;
        foreach ($collection->getAllIds() as $customerId) {
            // approve customer account
            $customer = $this->helperData->getCustomerById($customerId);
            if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())
                || in_array($this->helperData->getIsApproved($customerId), [AttributeOptions::APPROVED, AttributeOptions::NOTAPPROVE])
            ) {
                continue;
            }

            $this->helperData->approvalCustomerById($customerId, TypeAction::EDITCUSTOMER);
            $customersUpdated++;
        }

        if ($customersUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were approved.', $customersUpdated));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
