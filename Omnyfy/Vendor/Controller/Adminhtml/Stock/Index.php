<?php
namespace Omnyfy\Vendor\Controller\Adminhtml\Stock;

class Index extends \Omnyfy\Vendor\Controller\Adminhtml\AbstractAction
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
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Product Of ' . $sourceName));
        $resultPage->addBreadcrumb(__('Omnyfy'), __('Omnyfy'));
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        return $resultPage;
    }
}