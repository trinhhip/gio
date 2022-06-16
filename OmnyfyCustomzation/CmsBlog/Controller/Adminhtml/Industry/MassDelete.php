<?php
/**
 * Project: CMS Industry M2.
 * User: abhay
 * Date: 01/05/17
 * Time: 2:30 PM
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Industry;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Industry\CollectionFactory;

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
     * @var OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Industry\CollectionFactory;
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
        $industrysUpdated = 0;
        foreach ($collection as $industry) {
            $industry->delete();
            $industrysUpdated++;
        }

        if ($industrysUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $industrysUpdated));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('cms/industry/index');
        return $resultRedirect;
    }

}
