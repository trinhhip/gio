<?php
namespace Omnyfy\Vendor\Plugin\InventoryAdminUi\Controller;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\Model\Session;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class EditPlugin
{
    protected $session;
    protected $redirectFactory;
    protected $messageManager;

    public function __construct(
        Session $session,
        RedirectFactory $redirectFactory,
        MessageManagerInterface $messageManager
    ) {
        $this->session = $session;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute($subject, callable $proceed) {
        if (!$this->session->getVendorInfo()) {
            $result = $this->redirectFactory->create();
            $this->messageManager->addErrorMessage(
                __('Marketplace Owner can\'t create Source.')
            );
            $result->setPath('inventory/source/index');

            return $result;
        } else {
            return $proceed();
        }
    }
}
