<?php


namespace Omnyfy\Enquiry\Controller\Adminhtml\Enquiries;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('enquiries_id');
        
            $model = $this->_objectManager->create('Omnyfy\Enquiry\Model\Enquiries')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Enquiries no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        
            $model->setData($data);
        
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Enquiries.'));
                $this->dataPersistor->clear('omnyfy_enquiry_enquiries');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['enquiries_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Enquiries.'));
            }
        
            $this->dataPersistor->set('omnyfy_enquiry_enquiries', $data);
            return $resultRedirect->setPath('*/*/edit', ['enquiries_id' => $this->getRequest()->getParam('enquiries_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
