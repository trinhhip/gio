<?php

namespace Omnyfy\Vendor\Controller\Adminhtml\Inventory;

class InlineEdit extends \Omnyfy\Vendor\Controller\Adminhtml\AbstractAction
{
    // const ADMIN_RESOURCE = 'Omnyfy_Vendor::inventory';

    protected $resultJsonFactory;
    protected $collection;
    protected $sourceItemCollectionFactory;
    protected $inventoryHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Omnyfy\Vendor\Model\Resource\Inventory\Collection $collection,
        \Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory  $sourceItemCollectionFactory,
        \Omnyfy\Vendor\Helper\Inventory $inventoryHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collection = $collection;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->inventoryHelper = $inventoryHelper;
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
        $sourceItemCollection = $this->sourceItemCollectionFactory->create();
        foreach ($postItems as $item) {
            $model = $this->collection->getItemById($item['inventory_id']);

            /** Check if quantity change, save new quantity */
            if ($model->getQuantity() != $item['quantity']) {
                /** Save Quantity per Source */
                $oldQty = $model->getQuantity();
                $incrementStockQty = $item['quantity'] - $oldQty;
                $data = $this->inventoryHelper->getSourceCodeStockIdSku($item['inventory_id']);
                if (!empty($data)) {
                    $model->setQuantity($item['quantity']);
                    $model->save();

                    /** Save to core table */
                    $sourceItem = $sourceItemCollection->addFieldToFilter('sku', $data['sku'])
                        ->addFieldToFilter('source_code', $data['source_code'])
                        ->getFirstItem();
                    $sourceItem->setQuantity($item['quantity']);
                    $sourceItem->save();

                    /** Save stock quantity */
                    $conn = $this->inventoryHelper->getResourceConnection()->getConnection();
                    $stockTableName = 'inventory_stock_' . $data['stock_id'];
                    $sku = $data['sku'];
                    $oldStockSelect = $conn->select()->from($stockTableName, 'quantity')->where("sku = '$sku'");
                    $oldStockQty = $conn->fetchOne($oldStockSelect);
                    $newStockQty = $oldStockQty + $incrementStockQty;
                    $conn->update($stockTableName, ['quantity' => $newStockQty], "sku = '$sku'");
                }
            }
        }

        return $resultJson->setData([
            'messages' => $this->getErrorMessages(),
            'error' => $this->isErrorExists()
        ]);
    }

    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    protected function isErrorExists()
    {
        return (bool) $this->getMessageManager()->getMessages(true)->getCount();
    }
}
