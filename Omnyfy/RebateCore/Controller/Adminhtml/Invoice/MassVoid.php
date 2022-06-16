<?php

namespace Omnyfy\RebateCore\Controller\Adminhtml\Invoice;

use Exception;
use Omnyfy\RebateCore\Api\Data\IRebateInvoiceRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Omnyfy\RebateCore\Ui\Form\StatusInvoiceRebate;

/**
 * Class MassVoid
 */
class MassVoid extends Action
{
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var IRebateInvoiceRepository
     */
    protected $rebateInvoiceRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param IRebateInvoiceRepository $rebateInvoiceRepository
     */
    public function __construct(Context $context, Filter $filter, IRebateInvoiceRepository $rebateInvoiceRepository)
    {
        $this->filter = $filter;
        $this->rebateInvoiceRepository = $rebateInvoiceRepository;
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
        $collection = $this->filter->getCollection($this->rebateInvoiceRepository->getAllRebatesInvoice());
        $collectionSize = $collection->getSize();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        $checkPaidCondition = $this->checkPaidCondition($collection);
        if (!$checkPaidCondition) {
            foreach ($collection as $invoice) {
                if ($invoice->getStatus() == StatusInvoiceRebate::PENDING_PAYMENT) {
                    $invoice->setStatus(StatusInvoiceRebate::VOID_STATUS);
                    $invoice->save();
                }
            }
            $this->messageManager->addSuccess(__('The selected invoices have been marked as Voided'));
            return $resultRedirect;
        }
        $this->messageManager->addError(__('The invoice(s) you selected cannot be Voided, as they have already been paid, marked as void or have not yet been billed')); 
        return $resultRedirect;
    }

    public function checkPaidCondition($collection){
        foreach ($collection as $invoice) {
            if ($invoice->getStatus() != StatusInvoiceRebate::PENDING_PAYMENT) {
                return true;
            }
        }
        return false;
    }
}
