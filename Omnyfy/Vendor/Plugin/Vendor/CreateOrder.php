<?php

namespace Omnyfy\Vendor\Plugin\Vendor;

class CreateOrder
{
    private $resultRedirectFactory;

    private $messageManager;
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $session;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Backend\Model\Session $session
    )
    {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->messageManager = $context->getMessageManager();
        $this->session = $session;
    }

    public function aroundExecute(\Magento\Sales\Controller\Adminhtml\Order\Create\Start $subject, callable $process) {
        $vendorInfo = $this->session->getVendorInfo();
        if (empty($vendorInfo)) {
            $this->messageManager->addErrorMessage(__('Only vendors can create order here'));
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }
        return $process();
    }
}