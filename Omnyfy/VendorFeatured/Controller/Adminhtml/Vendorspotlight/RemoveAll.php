<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Vendorspotlight;

class RemoveAll extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::vendor_spotlight';
    protected $dataPersistor;
    protected $vendorFactory;
    protected $bannerVendorFactory;
    protected $clicksFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerVendorFactory $bannerVendorFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightClicksFactory $clicksFactory
    ){
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->vendorFactory = $vendorFactory;
        $this->bannerVendorFactory = $bannerVendorFactory;
        $this->clicksFactory = $clicksFactory;
    }

    public function execute(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $postdata = $this->getRequest()->getParams();
        $vendorId = $postdata['vendor_id'];

        if (isset($vendorId) && $vendorId!=null) {
            try {
                $banners = $this->bannerVendorFactory->create()->getBannersByVendorId($vendorId);
                foreach ($banners as $banner) {
                    $bannerVendorId = $banner->getBannerVendorId();
                    $clicks = $this->clicksFactory->create()->deleteClicksByBannerVendorId($bannerVendorId);
                    $modelBanner = $this->bannerVendorFactory->create()->load($bannerVendorId);
                    $modelBanner->delete();
                }
                $vendor = $this->vendorFactory->create()->load($vendorId);
                $message = "Ad Assignments for Vendor ".$vendor->getName(). " have been removed successfully.";
                $this->messageManager->addSuccess(__($message));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }else{
            $this->messageManager->addError(__('Vendor not found.'));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
