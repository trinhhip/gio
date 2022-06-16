<?php

namespace Omnyfy\Vendor\Plugin\InventoryAdminUi\Controller;

/**
 * Auto generate source_code
 */
class SavePlugin
{
    /**
     * @var \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory
     */
    protected $sourceCollectionFactory;

    /**
     * @param CollectionFactory $sourceCollectionFactory
     */
    public function __construct(
        \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory
    ) {
        $this->sourceCollectionFactory = $sourceCollectionFactory;
    }

    public function beforeExecute(\Magento\InventoryAdminUi\Controller\Adminhtml\Source\Save $subject)
    {
        $requestData = $subject->getRequest()->getPost()->toArray();
        $generalData = $requestData['general'];
        if (isset($generalData['source_code']) && empty($generalData['source_code'])) {
            $vendorId = $generalData['vendor_id'];
            $countSource = $this->countVendorSource($vendorId);
            $incrementSource = ltrim(100000 + $countSource + 1, '1');
            $sourceCode = $vendorId . '_' . $incrementSource;
            $generalData['source_code'] = $sourceCode;
            $subject->getRequest()->setPostValue('general', $generalData);
        }
    }

    /**
     * @param int $vendorId
     * @return int
     */
    public function countVendorSource(int $vendorId)
    {
        $sourceCollection = $this->sourceCollectionFactory->create();
        return $sourceCollection->addFieldToFilter('vendor_id', $vendorId)->getSize();
    }
}
