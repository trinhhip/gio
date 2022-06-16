<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\User\Type;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\UserType\CollectionFactory;

/**
 * Class MassDelete
 */
class MassDelete extends Action
{

    /**
     * @var Filter $filter
     */
    protected $filter;

    /**
     * @var OmnyfyCustomzation\CmsBlog\Model\ResourceModel\UserType\CollectionFactory;
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context, Registry $coreRegistry, ForwardFactory $resultForwardFactory, PageFactory $resultPageFactory, Filter $filter, CollectionFactory $collectionFactory
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute controller
     * @return Magento\Framework\Controller\ResultFactor
     */
    public function execute()
    {

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $userTypesUpdated = 0;
        foreach ($collection as $userType) {
            $userType->delete();
            $userTypesUpdated++;
        }

        if ($userTypesUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $userTypesUpdated));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('cms/user_type/index');
        return $resultRedirect;
    }

}
