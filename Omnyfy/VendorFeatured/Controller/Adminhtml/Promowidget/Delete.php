<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Promowidget;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::promo_widget';

    protected $coreRegistry;
    protected $promoFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Omnyfy\VendorFeatured\Model\PromoVendorWidgetFactory $promoFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Omnyfy\VendorFeatured\Model\PromoVendorWidgetFactory $promoFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->promoFactory = $promoFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $model = $this->promoFactory->create()->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Promotional Vendor Widget has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('Promotional Vendor Widget is not found.'));
        return $resultRedirect->setPath('*/*/');
    }
}