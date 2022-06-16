<?php

namespace Omnyfy\RebateCore\Controller\Adminhtml\Rebate;

use Exception;
use Omnyfy\RebateCore\Api\Data\IRebateRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassEnable
 */
class MassEnable extends Action
{
    /**
     * Const
     */
    const REBATE_ENABLE = 1;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var IRebateRepository
     */
    protected $rebateRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param IRebateRepository $rebateRepository
     */
    public function __construct(Context $context, Filter $filter, IRebateRepository $rebateRepository)
    {
        $this->filter = $filter;
        $this->rebateRepository = $rebateRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException|Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->rebateRepository->getAllRebates());
        $collectionSize = $collection->getSize();
        foreach ($collection as $attachment) {
            $attachment->setData('status', $this::REBATE_ENABLE);
            $attachment->save();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been enable.', $collectionSize));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('rebate_ui/index/index');
    }
}
