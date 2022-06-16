<?php
namespace OmnyfyCustomzation\BuyerApproval\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use OmnyfyCustomzation\BuyerApproval\Helper\Data;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\TypeAction;

/**
 * Class Approve
 *
 * @package OmnyfyCustomzation\BuyerApproval\Controller\Adminhtml\Index
 */
class Approve extends Action
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Approve constructor.
     *
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Data $helper
    ) {
        $this->helperData = $helper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/index');

        $customerId = (int)$this->getRequest()->getParam('id', 0);
        if (!$customerId) {
            return $resultRedirect;
        }

        $customer = $this->helperData->getCustomerById($customerId);
        if (!$this->helperData->isEnabledForWebsite($customer->getWebsiteId())) {
            return $resultRedirect;
        }

        $approveStatus = $this->getRequest()->getParam('status');
        try {
            if ($approveStatus === AttributeOptions::APPROVED) {
                $this->helperData->approvalCustomerById($customerId, TypeAction::EDITCUSTOMER);
                $this->messageManager->addSuccessMessage(__('Buyer account has been approved!'));
            } else {
                $this->helperData->notApprovalCustomerById($customerId);
                $this->messageManager->addSuccessMessage(__('Buyer account has been rejected!'));
            }
        } catch (Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __($exception->getMessage()));
        }

        $resultRedirect->setPath('customer/*/edit', ['id' => $customerId]);

        return $resultRedirect;
    }
}
