<?php
namespace Omnyfy\VendorFeatured\Controller\Spotlight;

class AjaxSaveClick extends \Magento\Framework\App\Action\Action
{
    protected $jsonFactory;
    protected $bannerVendorFactory;
    protected $clicksFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightBannerVendorFactory $bannerVendorFactory,
        \Omnyfy\VendorFeatured\Model\SpotlightClicksFactory $clicksFactory
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->bannerVendorFactory = $bannerVendorFactory;
        $this->clicksFactory = $clicksFactory;
    }

    public function execute(){
        $data['error'] = true;
        $data['message'] = "";
        $bannerVendorId = $this->getRequest()->getParam('banner_vendor_id');
        if ($bannerVendorId != null) {
            try {
                $bannerVendor = $this->bannerVendorFactory->create()->load($bannerVendorId);
                if ($bannerVendor->getBannerVendorId()) {
                    $model = $this->clicksFactory->create();
                    $model->setData('banner_vendor_id', $bannerVendorId);
                    $model->setData('created_at', date('Y-m-d H:i:s'));
                    $model->save();

                    $data['error'] = false;
                    $data['message'] = 'click has been saved';
                }else{
                    $data['error'] = true;
                    $data['message'] = 'banner vendor with id '.$bannerVendorId.' does not exist';
                }
            } catch (\Exception $e) {
                $data['error'] = true;
                $data['message'] = $e->getMessage();
            }
        }else{
            $data['error'] = true;
            $data['message'] = 'banner_vendor_id not found';
        }
        return $this->jsonFactory->create()->setData($data);
    }
}
