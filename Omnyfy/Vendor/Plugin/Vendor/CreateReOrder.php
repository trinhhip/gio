<?php

namespace Omnyfy\Vendor\Plugin\Vendor;

class CreateReOrder
{
    private $resultRedirectFactory;

    private $messageManager;

    private $_request;

    public function __construct(
        \Magento\Framework\App\Action\Context $context
    ){
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->messageManager = $context->getMessageManager();
        $this->_request = $context->getRequest();
    }

    public function aroundExecute(\Magento\Sales\Controller\Adminhtml\Order\Create\Reorder $subject, callable $process) {
        $orderId = $this->_request->getParam('order_id');
        $this->messageManager->addErrorMessage(__('No Back-end user can Re-order'));
        return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
