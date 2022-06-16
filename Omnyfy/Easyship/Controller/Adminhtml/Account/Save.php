<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\Account;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_Easyship::easyshipaccount';

    protected $userContext;
    protected $accountFactory;
    protected $rateFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Omnyfy\Easyship\Model\EasyshipAccountFactory $accountFactory,
        \Omnyfy\Easyship\Model\EasyshipRateOptionFactory $rateFactory
    ){
        parent::__construct($context);
        $this->userContext = $userContext;
        $this->accountFactory = $accountFactory;
        $this->rateFactory = $rateFactory;
    }

    public function execute(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $accountData = $this->getRequest()->getParams();
        $accountId = $this->getRequest()->getParam('entity_id');
        $vendorInfo = $this->_session->getVendorInfo();

        if ($accountData) {
            $accountModel = $this->accountFactory->create();
            $rateModel = $this->rateFactory->create();

            if (isset($accountId) && $accountId != null) {
                $accountModel->load($accountId);
            }else{
                if (isset($accountData['entity_id'])) {
                    unset($accountData['entity_id']);
                }
                if ($vendorInfo) {
                    $accountData['created_by_mo'] = 0;
                    $accountData['created_by'] = $vendorInfo['vendor_id'];
                }else{
                    $accountData['created_by_mo'] = 1;
                    $accountData['created_by'] = $this->userContext->getUserId();
                }
            }

            try {
                $accountModel->setData($accountData);
                $accountModel->save();
                $accountId = $accountModel->getId();

                $rate = $this->rateFactory->create()->getRateOptionByAccountId($accountId);
                if ($rate) {
                    $rateModel->load($rate->getId());
                }
                $rateModel->setIsActive(1);
                $rateModel->setShippingRateOptionPrice($accountData['fixed_rate']);
                $rateModel->setName($accountData['fixed_rate_name']);
                $rateModel->setEasyshipAccountId($accountId);
                $rateModel->save();

                $this->messageManager->addSuccess(__('Easyship account saved successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $accountId]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
