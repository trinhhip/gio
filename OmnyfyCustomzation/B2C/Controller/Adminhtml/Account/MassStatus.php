<?php


namespace OmnyfyCustomzation\B2C\Controller\Adminhtml\Account;


use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use OmnyfyCustomzation\B2C\Model\ResourceModel\BuyerAccount\CollectionFactory;
use OmnyfyCustomzation\B2C\Helper\Data as HelperData;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use OmnyfyCustomzation\BuyerApproval\Helper\Data as ApprovalHelper;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\TypeAction;

class MassStatus extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;
    /**
     * @var HelperData
     */
    protected $helperData;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var ApprovalHelper
     */
    protected $approvalHelper;

    public function __construct(
        CollectionFactory $collectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        HelperData $helperData,
        Filter $filter,
        Action\Context $context,
        ApprovalHelper $approvalHelper
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->helperData = $helperData;
        $this->filter = $filter;
        $this->approvalHelper = $approvalHelper;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $status = $this->getRequest()->getParam('status');
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $customerEmail = [];
        try {
            foreach ($collection as $item) {
                if ($item->getStatus() == AttributeOptions::UNREGISTERED) {
                    $this->messageManager->addWarningMessage(__('Email %1 not registered', $item->getEmail()));
                } else {
                    $customerEmail[] = $item->getEmail();
                    $item->setData('status', $status);
                    $item->save();
                }
            }
            $customers = $this->customerCollectionFactory->create();
            $customers->addFieldToFilter('email', ['in' => $customerEmail]);
            foreach ($customers as $customer) {
                switch ($status) {
                    case AttributeOptions::APPROVED:
                        $this->approvalHelper->approvalCustomerById($customer->getId(), TypeAction::EDITCUSTOMER);
                        break;
                    case AttributeOptions::NOTAPPROVE:
                        $this->approvalHelper->notApprovalCustomerById($customer->getId());
                }

                $this->_eventManager->dispatch(
                    'after_change_approval_status',
                    ['customer' => $customer, 'approval_status' => $status]);
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated', $customers->getSize()));
        } catch (Exception $e) {
            $this->messageManager->addSuccessMessage($e->getMessage());
            $this->messageManager->addErrorMessage(__('An unknown error has occurred'));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
