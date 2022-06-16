<?php


namespace OmnyfyCustomzation\Vendor\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use OmnyfyCustomzation\Vendor\Helper\Url;

class VendorSaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Url
     */
    private $helperUrl;
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * VendorSaveBefore constructor.
     * @param Url $helperUrl
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Url $helperUrl,
        ManagerInterface $messageManager
    )
    {
        $this->helperUrl = $helperUrl;
        $this->messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        $vendor = $observer->getVendor();
        $urlKey = $vendor->getUrlKey();
        if ($urlKey) {
            if ($this->helperUrl->isDuplicateUrl($urlKey, $vendor->getId())) {
                while ($this->helperUrl->isDuplicateUrl($urlKey, $vendor->getId())) {
                    $urlKey = $this->helperUrl->newUrl($urlKey);
                }
                $this->messageManager->addWarningMessage(__('Url key %1 have changed to %2 because %1 already exists', $vendor->getUrlKey(), $urlKey));
                $vendor->setUrlKey($urlKey);
            }
        } else {
            $newUrlKey = $this->helperUrl->generateUrl($vendor->getName());
            while ($this->helperUrl->isDuplicateUrl($newUrlKey, $vendor->getId())) {
                $newUrlKey = $this->helperUrl->newUrl($newUrlKey);
            }
            $vendor->setUrlKey($newUrlKey);
        }
    }
}