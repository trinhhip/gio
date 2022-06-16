<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Spotlightbanner;

class AssignVendors extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::spotlight_banner';
    protected $coreRegistry;
    protected $resultPageFactory;
    protected $bannerFactory;
    protected $bannerVendorFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Omnyfy\VendorFeatured\Model\SpotlightBannerPlacementFactory $bannerFactory
     * @param \Omnyfy\VendorFeatured\Model\SpotlightBannerVendorFactory $bannerVendorFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerPlacementFactory $bannerFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerVendorFactory $bannerVendorFactory
    ){
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->bannerFactory = $bannerFactory;
        $this->bannerVendorFactory = $bannerVendorFactory;
    }

    public function execute()
    {
        $bannerId = $this->getRequest()->getParam('banner_id');
        $model = $this->bannerVendorFactory->create()->getVendorsOnBanner($bannerId);
        $banner = $this->bannerFactory->create();
        if ($bannerId) {
            $banner->load($bannerId);
            if (!$banner->getBannerId()) {
                $this->messageManager->addErrorMessage(__('This Banner Placement is no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('omnyfy_vendorfeatured_spotlight_assignvendors', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $title = $banner->getBannerId() ? "Assign Vendors to ".$banner->getBannerName()." Banner" : "Assign Vendors to Banner";
        $resultPage->getConfig()->getTitle()->prepend(__($title));
        return $resultPage;
    }
}