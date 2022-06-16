<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Account;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_Easyship::easyshipaccount';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountFactory
    ) {
        $this->accountFactory = $accountFactory;
        parent::__construct($context);
    }  
    
    public function execute(){
        if (!$this->getRequest()->isPost()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Page not found.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->accountFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Account deleted successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }else{
            $this->messageManager->addErrorMessage(__('We can\'t find that account.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
