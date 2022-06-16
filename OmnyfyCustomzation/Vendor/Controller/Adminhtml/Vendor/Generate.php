<?php

namespace OmnyfyCustomzation\Vendor\Controller\Adminhtml\Vendor;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory;
use OmnyfyCustomzation\Vendor\Helper\Url;

class Generate extends Action
{
    /**
     * @var CollectionFactory
     */
    public $vendorCollection;
    /**
     * @var Url
     */
    public $helperUrl;
    /**
     * @var ManagerInterface
     */
    public $messageManager;

    public function __construct(
        Context $context,
        CollectionFactory $vendorCollection,
        Url $helperUrl,
        ManagerInterface $messageManager

    )
    {
        $this->vendorCollection = $vendorCollection;
        $this->helperUrl = $helperUrl;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $count = 0;
        $vendors = $this->vendorCollection->create();
        foreach ($vendors as $vendor) {
            if (!$vendor->getUrlKey()) {
                $urlKey = $this->helperUrl->generateUrl($vendor->getName());
                $vendor->setUrlKey($urlKey);
                $vendor->save();
                $count++;
            }
        }
        $this->messageManager->addSuccessMessage(__('%1 vendor has been updated', $count));
        $this->_redirect('omnyfy_vendor/*/');
    }
}