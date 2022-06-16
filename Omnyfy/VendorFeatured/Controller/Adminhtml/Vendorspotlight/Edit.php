<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Vendorspotlight;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::vendor_spotlight';

    protected $coreRegistry;
    protected $resultPageFactory;
    protected $vendorFactory;
    protected $bannerVendorFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Omnyfy\Vendor\Model\VendorFactory $vendorFactory
     * @param \Omnyfy\VendorFeatured\Model\SpotlightBannerVendorFactory $bannerVendorFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerVendorFactory $bannerVendorFactory

    ){
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->vendorFactory = $vendorFactory;
        $this->bannerVendorFactory = $bannerVendorFactory;
    }

    public function execute()
    {
        $vendorId = $this->getRequest()->getParam('vendor_id');
        $model = $this->bannerVendorFactory->create()->getBannersByVendorId($vendorId);
        $vendor = $this->vendorFactory->create();

        if ($vendorId) {
            $vendor->load($vendorId);
            if (!$vendor->getId()) {
                $this->messageManager->addErrorMessage(__('This Banner Placement is no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('omnyfy_vendorfeatured_vendor_spotlight', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $title = $vendor->getId() ? "Edit Vendor ".$vendor->getName()." Ad Assignments" : "Edit Vendor Ad Assignments";

        $resultPage->getConfig()->getTitle()->prepend(__($title));
        return $resultPage;
    }
}