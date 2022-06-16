<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Tool\Template;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\ToolTemplate\CollectionFactory;

/**
 * Class MassStatus
 */
class MassStatusUpdate extends Action
{

    /**
     * @var Filter $filter
     */
    protected $filter;

    /**
     * @var OmnyfyCustomzation\CmsBlog\Model\ResourceModel\ToolTemplate\CollectionFactory;
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
        $this->_coreRegistry = $coreRegistry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute controller
     * @return Magento\Framework\Controller\ResultFactor
     */
    public function execute()
    {

        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $userTypeStatus = $this->getRequest()->getParam('status');

        $toolTemplatesUpdated = 0;
        foreach ($collection as $toolTemplate) {
            $toolTemplate->setData(
                'status', $userTypeStatus
            );
            $toolTemplate->save();
            $toolTemplatesUpdated++;
        }

        if ($toolTemplatesUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $toolTemplatesUpdated));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('cms/tool_template/index');
        return $resultRedirect;
    }

}
