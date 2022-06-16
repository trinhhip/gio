<?php


namespace OmnyfyCustomzation\OrderStatus\Controller\Adminhtml\Order;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassChangeStatus extends Action
{
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    public function __construct(
        Filter $filter,
        Context $context,
        CollectionFactory $orderCollectionFactory
    )
    {
        $this->filter = $filter;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $status = $this->getRequest()->getParam('status');
        $collection  = $this->filter->getCollection($this->orderCollectionFactory->create());
        foreach ($collection as $item) {
            $item->addCommentToStatusHistory(__('Change status %1 to %2.',$item->getStatus(), $status));
            $item->setStatus($status);
            $item->save();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been modified.', $collection->getSize()));

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('sales/order');
    }
}