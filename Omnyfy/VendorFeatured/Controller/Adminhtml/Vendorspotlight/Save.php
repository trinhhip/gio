<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Vendorspotlight;

class Save extends \Magento\Backend\App\Action
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
        if (isset($postdata['main_vendor_id']) && $postdata['main_vendor_id']!=null) {
            try {
                $vendorId = $postdata['main_vendor_id'];
                $banners = $this->bannerVendorFactory->create()->getBannersByVendorId($vendorId);
                $deletedBannerVendorIds = [];
                foreach ($banners as $banner) {
                    $deletedBannerVendorIds[] = $banner->getBannerVendorId();
                }
                if (isset($postdata['vendor_spotlight_container'])) {
                    foreach ($postdata['vendor_spotlight_container'] as $spotlight) {
                        $searchBanner = array_search($spotlight['banner_vendor_id'], $deletedBannerVendorIds);
                        if ($searchBanner !== false) {
                            unset($deletedBannerVendorIds[$searchBanner]);
                        }
                    }
                }
                if (count($deletedBannerVendorIds)) {
                    foreach ($deletedBannerVendorIds as $id) {
                        $clicks = $this->clicksFactory->create()->deleteClicksByBannerVendorId($id);
                        $modelBanner = $this->bannerVendorFactory->create()->load($id);
                        $modelBanner->delete();
                    }
                }
                $vendor = $this->vendorFactory->create()->load($vendorId);
                $message = "Ad Assignments for Vendor ".$vendor->getName(). " have been removed successfully.";
                $this->messageManager->addSuccess(__($message));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
