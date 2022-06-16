<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Spotlightbanner;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::spotlight_banner';
    protected $dataPersistor;
    protected $bannerFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerPlacementFactory $bannerFactory
    ){
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->bannerFactory = $bannerFactory;
    }

    public function execute(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $postdata = $this->getRequest()->getParams();
        if ($postdata) {
            $model = $this->bannerFactory->create();
            
            if (isset($postdata['banner_id']) && $postdata['banner_id'] != null) {
                $model->load($postdata['banner_id']);
            }else{
                $postdata['created_at'] = date("Y-m-d H:i:s");
            }

            if (isset($postdata['category_ids']) && is_array($postdata['category_ids'])) {
                $postdata['category_ids'] = implode(",", $postdata['category_ids']);
            }
            if (isset($postdata['vendor_ids']) && is_array($postdata['vendor_ids'])) {
                $postdata['vendor_ids'] = implode(",", $postdata['vendor_ids']);
            }

            try {
                $model->setData($postdata);
                $model->save();
                $this->messageManager->addSuccess(__('Spotlight Banner Placement saved successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}