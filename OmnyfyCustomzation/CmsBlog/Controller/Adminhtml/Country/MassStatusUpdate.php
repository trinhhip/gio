<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Country;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country\CollectionFactory;

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
     * @var OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country\CollectionFactory;
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

        $countryStatus = $this->getRequest()->getParam('status');

        $countrysUpdated = 0;
        foreach ($collection as $country) {
            $country->setData(
                'status', $countryStatus
            );
            $country->save();
            $countrysUpdated++;
        }

        if ($countrysUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $countrysUpdated));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('cms/country/index');
        return $resultRedirect;
    }

}
