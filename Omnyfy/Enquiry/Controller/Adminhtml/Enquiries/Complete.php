<?php


namespace Omnyfy\Enquiry\Controller\Adminhtml\Enquiries;

class Complete extends \Omnyfy\Enquiry\Controller\Adminhtml\Enquiries
{
    protected $_enquiryCollectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Omnyfy\Enquiry\Model\ResourceModel\Enquiries\CollectionFactory $enquiryCollectionFactory

    )
    {
        $this->_enquiryCollectionFactory = $enquiryCollectionFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('enquiries_id');
        if ($id) {
            try {
                $enquiries = $this->_enquiryCollectionFactory->create();
                $enquiries->addFilter('enquiries_id', ['eq' => $id]);

                foreach($enquiries as $enquiry){
                    $enquiry->setStatus(\Omnyfy\Enquiry\Model\Enquiries\Source\Status::COMPLETE_MESSAGE);
                    $enquiry->save();
                    // display success message
                    $this->messageManager->addSuccessMessage(__('Marked the enquiry complete.'));
                }

                return $resultRedirect->setPath('*/*/edit', ['enquiries_id' => $id]);
            }catch(\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['enquiries_id' => $id]);
            }
        } else {
            // display error message
            $this->messageManager->addErrorMessage(__('We can\'t find the enquiry to complete.'));
            // go to grid
            return $resultRedirect->setPath('*/*/');
        }
    }
}
