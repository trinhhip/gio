<?php
namespace Omnyfy\Mcm\Controller\Adminhtml\PayoutType;

class Index extends \Omnyfy\Mcm\Controller\Adminhtml\AbstractAction {
    protected $resourceKey = 'Omnyfy_Mcm::select_payout_type';
    protected $adminTitle = 'Payout Type';

    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Payout Type'));
        return $resultPage;
    }
}
