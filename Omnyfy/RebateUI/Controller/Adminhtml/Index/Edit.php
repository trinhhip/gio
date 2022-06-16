<?php

namespace Omnyfy\RebateUI\Controller\Adminhtml\Index;

use Omnyfy\RebateCore\Api\Data\IRebateRepository;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Maxime\Jobs\Model\Department;

/**
 * Class Edit
 * @package Omnyfy\RebateUI\Controller\Adminhtml\Index
 */
class Edit extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry = null;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var rebateRepository
     */
    protected $rebateRepository;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Department $model
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        IRebateRepository $rebateRepository,
        Registry $registry
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->rebateRepository = $rebateRepository;
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->rebateRepository->getRebate($id);
        if ($id) {
            if (!$model->getEntityId()) {
                $this->messageManager->addError(__('This Rebate not exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*');
            }
        }
        
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);

        }
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Rebate') : __('New Rebate'),
            $id ? __('Edit Rebate') : __('New Rebate')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Rebate'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Rebate'));
            
        return $resultPage;
    }


    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Omnyfy_RebateUI::rebate')
            ->addBreadcrumb(__('Rebate'), __('Rebate'))
            ->addBreadcrumb(__('Manage Rebate'), __('Manage Rebate'));
        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('omnyfy_rebate_ui::rebate_ui');
    }
}