<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Controller\Adminhtml\Rules;


use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Cms\Controller\Adminhtml\Block;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\ShippingCalculatedWeight\Model\CalculateWeight;

class Edit extends Block
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var CalculateWeight
     */
    private $calculateWeight;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param CalculateWeight $calculateWeight
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        CalculateWeight $calculateWeight
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->calculateWeight = $calculateWeight;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit CMS block
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->calculateWeight;

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This rules no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_coreRegistry->register('calc_rules', $model);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Rule') : __('New Rule'),
            $id ? __('Edit Rule') : __('New Rule')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Rule'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Rule'));
        return $resultPage;
    }
}