<?php
namespace Omnyfy\Vendor\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\Controller\ResultFactory; 

class Email extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\Email
{
    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try{
            return parent::execute();
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
