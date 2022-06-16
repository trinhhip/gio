<?php
/**
 * Project: Multi Vendors.
 * User: jing
 * Date: 5/2/18
 * Time: 12:03 AM
 */
namespace Omnyfy\Vendor\Controller\Adminhtml\Source;

class InlineEdit extends \Omnyfy\Vendor\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_Vendor::inventory';

    protected $resultJsonFactory;
    protected $inventoryResource;
    protected $sourceItemResource;
    protected $sourceItemFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Inventory\Model\ResourceModel\SourceItem $sourceItemResource,
        \Magento\Inventory\Model\SourceItemFactory $sourceItemFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sourceItemResource = $sourceItemResource;
        $this->sourceItemFactory = $sourceItemFactory;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $itemModel = $this->sourceItemFactory->create();
        foreach ($postItems as $item) {
            $sourceItem = $this->sourceItemResource->load($itemModel,$item['source_item_id'], 'source_item_id');
            $itemModel->setQuantity($item['quantity']);
            $this->sourceItemResource->save($itemModel);
        }

        return $resultJson->setData([
            'messages' => $this->getErrorMessages(),
            'error' => $this->isErrorExists()
        ]);
    }

    protected function getErrorMessages() {
        $messages = [];
        foreach($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    protected function isErrorExists() {
        return (bool) $this->getMessageManager()->getMessages(true)->getCount();
    }
}