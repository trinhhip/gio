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
class MassPayout extends Action
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
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param IRebateInvoiceRepository $rebateInvoiceRepository
     */
    public function __construct(
        Context $context, 
        Filter $filter, 
        IRebateInvoiceRepository $rebateInvoiceRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    )
    {
        $this->filter = $filter;
        $this->date = $date;
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
        $date = $this->date->gmtDate();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        $checkPaidCondition = $this->checkPaidCondition($collection);
        if (!$checkPaidCondition) {
        	foreach ($collection as $invoice) {
	        	if ($invoice->getStatus() == StatusInvoiceRebate::PENDING_PAYMENT || $invoice->getStatus() == StatusInvoiceRebate::VOID_STATUS) {
	        		$invoice->setStatus(StatusInvoiceRebate::PAID_STATUS);
		            $invoice->setPayoutDate($date);
		            $invoice->save();
	        	}
	        }
	        $this->messageManager->addSuccess(__('The selected invoices have been marked as paid'));
	        return $resultRedirect;
        }
        $this->messageManager->addError(__('The invoice(s) you selected cannot be marked as paid, as they are either paid already or not yet billed')); 
        return $resultRedirect;
    }

    public function checkPaidCondition($collection){
    	foreach ($collection as $invoice) {
        	if ($invoice->getStatus() != StatusInvoiceRebate::PENDING_PAYMENT && $invoice->getStatus() != StatusInvoiceRebate::VOID_STATUS) {
        		return true;
        	}
        }
        return false;
    }
}
