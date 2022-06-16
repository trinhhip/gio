<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Controller\Adminhtml\Rules;


use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use OmnyfyCustomzation\ShippingCalculatedWeight\Model\ResourceModel\CalculateWeight\CollectionFactory;

class MassStatus extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Filter $filter,
        Action\Context $context
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $status = $this->getRequest()->getParam('status');
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        try {
            foreach ($collection as $item) {
                $item->setData('status', $status);
                $item->save();
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', $collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An unknown error has occurred'));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
