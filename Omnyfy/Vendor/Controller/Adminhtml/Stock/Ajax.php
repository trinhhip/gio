<?php
namespace Omnyfy\Vendor\Controller\Adminhtml\Stock;

use Magento\Framework\Controller\ResultFactory;

class Ajax extends \Magento\Backend\App\Action
{
    protected $registry;
    protected $resultFactory;
    protected $session;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Session $session
    ) {
        parent::__construct($context);
        $this->session = $session;
    }

    public function execute()
    {
        $vendorId = $this->getRequest()->getPost('vendor_id');
        $this->session->setAjaxVendorId($vendorId);
           
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/session_ajax_vendor_id.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($this->session->getAjaxVendorId());
    }
}