<?php

namespace Omnyfy\Vendor\Observer;

class SaveSourceStock implements \Magento\Framework\Event\ObserverInterface
{
    protected $vendorSourceStockFactory;
    protected $collectionFactory;
    protected $modelFactory;
    protected $sourceCollectionFactory;
    /**
     * @var \Magento\Backend\Model\Session
     */
    private $backendSession;

    public function __construct(
        \Omnyfy\Vendor\Model\VendorSourceStockFactory $modelFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vendorSourceStockFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock\CollectionFactory $collectionFactory,
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->modelFactory = $modelFactory;
        $this->vendorSourceStockFactory = $vendorSourceStockFactory;
        $this->collectionFactory = $collectionFactory;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->backendSession = $backendSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getRequest()->getPostValue();
        $stockId = $observer->getStock()->getStockId();

        if (isset($data['sources']['assigned_sources'])) {
            $vendorId = $this->sourceCollectionFactory->create()->getItemById($data['sources']['assigned_sources'][0]['source_code'])->getVendorId();
            $this->save($data, $stockId, $vendorId);
        } else {
            $collection = $this->collectionFactory->create()->addFieldToFilter('stock_id', $stockId);
            if ($collection->getSize() > 0) {
                foreach ($collection->getItems() as $item) {
                    $item->delete();
                }
            }
        }
    }

    protected function save($data, $stockId, $vendorId)
    {
        $isVendorAdmin = true;
        if(empty($this->backendSession->getVendorInfo())){
            $isVendorAdmin = false;
        }
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('stock_id', $stockId);
        $dataAssign = $data['sources']['assigned_sources'];
        $model = $this->modelFactory->create();
        /** save new data to table omnyfy_vendor_source_code */
        if ($collection->getSize() < 1) {
            foreach ($dataAssign as $dataItem) {
                // $model = $this->modelFactory->create();
                if(!$isVendorAdmin){
                    $vendorId = $this->vendorSourceStockFactory->getVendorIdBySourceCode($dataItem['source_code']);
                }
                $model->setStockId($stockId);
                $model->setSourceCode($dataItem['source_code']);
                $model->setVendorId($vendorId);
                $model->setSourceStock($dataItem['source_code'] . '_' . $stockId);
                $model->save();
                $model->unsetData();
            }
        } elseif ($collection->getSize() > 0) {
            /** case update table omnyfy_vendor_source_code */
            /** add new data if assign more source */
            foreach ($dataAssign as $assignItem) {
                $dataItem = $this->collectionFactory->create()->addFieldToFilter('stock_id', $stockId)
                    ->addFieldToFilter('main_table.source_code', $assignItem['source_code'])->getFirstItem()->getData();

                /** if can not find item in collection => add new data */
                if (empty($dataItem)) {
                    // $model = $this->modelFactory->create();
                    if(!$isVendorAdmin){
                        $vendorId = $this->vendorSourceStockFactory->getVendorIdBySourceCode($assignItem['source_code']);
                    }
                    if ($vendorId != 0) {
                        $model->setStockId($stockId);
                        $model->setSourceCode($assignItem['source_code']);
                        $model->setVendorId($vendorId);
                        $model->save();
                        $model->unsetData();
                    }
                }
            }

            /** delete data if unassgin source */
            $collectionCheck = $this->collectionFactory->create()->addFieldToFilter('stock_id', $stockId);
            if ($collectionCheck->getSize() > 0) {
                foreach ($collectionCheck->getItems() as $item) {
                    $sourceCode = $item->getSourceCode();
                    $arrAsignSource = [];
                    foreach ($dataAssign as $assignItem) {
                        array_push($arrAsignSource, $assignItem['source_code']);
                    }
                    if (!in_array($sourceCode, $arrAsignSource)) {
                        $item->delete();
                    }
                }
            }
        }
    }
}
