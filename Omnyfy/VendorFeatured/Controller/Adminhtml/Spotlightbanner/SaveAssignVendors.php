<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Spotlightbanner;

class SaveAssignVendors extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::spotlight_banner_assign';
    protected $dataPersistor;
    protected $bannerFactory;
    protected $bannerVendorFactory;
    protected $clicksFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerPlacementFactory $bannerFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerVendorFactory $bannerVendorFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightClicksFactory $clicksFactory
    ){
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->bannerFactory = $bannerFactory;
        $this->bannerVendorFactory = $bannerVendorFactory;
        $this->clicksFactory = $clicksFactory;
    }

    public function execute(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $postdata = $this->getRequest()->getParams();
        $bannerId = $postdata['main_banner_id'];
        if ($bannerId) {
            $banners = $this->bannerVendorFactory->create()->getBannersByBannerId($bannerId);
            $deletedBannerVendorIds = [];
            if (is_object($banners) && count($banners)) {
                foreach ($banners as $banner) {
                    $deletedBannerVendorIds[] = $banner->getBannerVendorId();
                }
            }
        }

        if (isset($postdata['assign_vendors_container'])) {
            try {
                foreach ($postdata['assign_vendors_container'] as $assign) {
                    $model = $this->bannerVendorFactory->create();
                    if (isset($assign['banner_vendor_id'])) {
                        //remove the banner_vendor_id from delete list
                        $searchBanner = array_search($assign['banner_vendor_id'], $deletedBannerVendorIds);
                        if ($searchBanner !== false) {
                            unset($deletedBannerVendorIds[$searchBanner]);
                        }
                        $model->load($assign['banner_vendor_id']);
                    }else{
                        $model->setData('banner_id', $bannerId);
                        $model->setData('vendor_id', $assign['vendor_id']);
                    }
                    $model->setData('sort_order', $assign['position']);
                    $model->save();
                }
                //delete the assigned vendor using banner_vendor_id
                if (count($deletedBannerVendorIds)) {
                    foreach ($deletedBannerVendorIds as $id) {
                        $clicks = $this->clicksFactory->create()->deleteClicksByBannerVendorId($id);
                        $modelBanner = $this->bannerVendorFactory->create()->load($id);
                        $modelBanner->delete();
                    }
                }
                $this->messageManager->addSuccess(__('Vendors have been assigned.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }

        }else{
            //$postdata['assign_vendors_container'] is not set, might be just delete assigned vendors
            try {
                if (count($deletedBannerVendorIds)) {
                    foreach ($deletedBannerVendorIds as $id) {
                        $clicks = $this->clicksFactory->create()->deleteClicksByBannerVendorId($id);
                        $modelBanner = $this->bannerVendorFactory->create()->load($id);
                        $modelBanner->delete();
                    }
                }
                $this->messageManager->addSuccess(__('No Vendor assigned.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}