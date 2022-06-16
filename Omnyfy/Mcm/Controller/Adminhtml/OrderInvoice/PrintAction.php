<?php

namespace Omnyfy\Mcm\Controller\Adminhtml\OrderInvoice;

use Amasty\Orderattr\Model\ConfigProvider;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

class PrintAction extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\PrintAction
{

    protected $printInvoice;

    protected $mcmHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Omnyfy\Mcm\Helper\PrintInvoice $printInvoice,
        array $data = []
    ) {
        $this->printInvoice = $printInvoice;
        parent::__construct($context, $fileFactory, $resultForwardFactory);
    }

    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');

        $invoice = $this->printInvoice->getInvoice($invoiceId);
        $vendors = [];
        foreach ($invoice->getAllItems() as $item) {
            $vendors[] = $item->getVendorId();
        }

        $vendor = $this->printInvoice->vendorInfo();

        $pdf = $this->printInvoice->getInvoicePdf(
                    $invoiceId, 
                    $vendor['vendor_id'] ? [$vendor['vendor_id']] : $vendors //Print All vendor items if no vendor login
                );

        return $this->_fileFactory->create(
            pathinfo($pdf, PATHINFO_BASENAME),
            [
                'type' => 'filename',
                'value' => $pdf,
                'rm' => 1
            ],
            DirectoryList::PUB,
            'application/pdf'
        );
    }
}
