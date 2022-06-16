<?php
namespace Omnyfy\VendorGallery\Controller\Adminhtml\Album;

use Magento\Backend\App\Action;
use Omnyfy\Vendor\Model\LocationFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory as SourceCollectionFactory;

class AjaxSource extends \Magento\Backend\App\Action
{
    protected $jsonHelper;
    protected $sourceCollectionFactory;

    public function __construct(
        Action\Context $context,
        SourceCollectionFactory $sourceCollectionFactory,
        Data $jsonHelper
    ) {
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    public function execute() {
        $vendorId = $this->getRequest()->getParam('vendorId');
        $sourceCollection = $this->sourceCollectionFactory->create();
        $result = $sourceCollection->addFieldToFilter('vendor_id', $vendorId)->toOptionArray();
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
    }
}