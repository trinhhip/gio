<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Promowidget;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::promo_widget';

    protected $dataPersistor;
    protected $promoFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Omnyfy\VendorFeatured\Model\PromoVendorWidgetFactory $promoFactory
    ){
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->promoFactory = $promoFactory;
    }

    public function execute(){
        $resultRedirect = $this->resultRedirectFactory->create();
        $promoData = $this->getRequest()->getParams();
        $id = $this->getRequest()->getParam('entity_id');

        if ($promoData) {
            $model = $this->promoFactory->create();

            if (isset($id) && $id != null) {
                $model->load($id);
            }else{
                $promoData['created_at'] = date("Y-m-d H:i:s");
            }
            try {
                $model->setData($promoData);
                $model->save();
                $this->dataPersistor->clear('omnyfy_vendorfeatured_vendor_tag');
                $this->messageManager->addSuccess(__('Promotional Vendor Widget has been saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }

        }
        return $resultRedirect->setPath('*/*/');
    }
}