<?php

namespace Omnyfy\Vendor\Controller\Adminhtml\Inventory;

class Inventory extends \Omnyfy\Vendor\Controller\Adminhtml\AbstractAction
{
    protected $adminTitle = 'Inventory';
    protected $sourceCollection;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Inventory\Model\ResourceModel\Source\Collection $collection
    ) {
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
        $this->sourceCollection = $collection;
    }

    public function execute()
    {
        $sourceCode = $this->getRequest()->getParam('source_code');
        $sourceName = '';
        if ($sourceCode) {
            $sourceName = $this->sourceCollection->getItemByColumnValue('source_code', $sourceCode)->getName();
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Product Of ' . $sourceName));
        $resultPage->addBreadcrumb(__('Omnyfy'), __('Omnyfy'));
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        return $resultPage;
    }
}