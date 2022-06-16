<?php
namespace Omnyfy\VendorFeatured\Controller\Adminhtml\Promowidget;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Omnyfy_VendorFeatured::promo_widget';

    protected $coreRegistry;
    protected $resultPageFactory;
    protected $promoFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Omnyfy\VendorFeatured\Model\PromoVendorWidgetFactory $promoFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Omnyfy\VendorFeatured\Model\PromoVendorWidgetFactory $promoFactory
    ){
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->promoFactory = $promoFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->promoFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addErrorMessage(__('This Promotional Vendor Widget is no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('omnyfy_vendorfeatured_promo_widget', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->getConfig()->getTitle()->prepend($model->getEntityId() ? __("Edit Promotional Vendor Widget") : __('New Promotional Vendor Widget'));
        return $resultPage;
    }
}
