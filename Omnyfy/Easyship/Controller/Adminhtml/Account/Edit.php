<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Account;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_Easyship::easyshipaccount';

    protected $resultPageFactory;
    protected $accountFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountFactory
    ){
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->accountFactory = $accountFactory;
    }

    public function execute(){
        $id = $this->getRequest()->getParam('id');
        $model = $this->accountFactory->create();
        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This account is no longer exists.'));
                $this->_redirect('omnyfy_easyship/*');
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($model->getEntityId() ? $model->getName() : __('New Easyship Account'));
        return $resultPage;
    }
}
