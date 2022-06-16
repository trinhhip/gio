<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Spotlightbanner;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::spotlight_banner';

    protected $coreRegistry;
    protected $resultPageFactory;
    protected $bannerFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Omnyfy\VendorFeatured\Model\SpotlightBannerPlacementFactory $bannerFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerPlacementFactory $bannerFactory
    ){
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->bannerFactory = $bannerFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('banner_id');
        $model = $this->bannerFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getBannerId()) {
                $this->messageManager->addErrorMessage(__('This Banner Placement is no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('omnyfy_vendorfeatured_spotlight_banner', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->getConfig()->getTitle()->prepend($model->getBannerId() ? __("Edit Banner Placement") : __("New Banner Placement"));
        return $resultPage;
    }
}