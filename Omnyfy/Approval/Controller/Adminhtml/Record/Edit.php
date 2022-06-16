<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 17:58
 */
namespace Omnyfy\Approval\Controller\Adminhtml\Record;

use Omnyfy\Vendor\Controller\Adminhtml\AbstractAction;

class Edit extends AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_Approval::record';

    protected $resourceKey = 'Omnyfy_Approval::record';

    protected $adminTitle = 'Review Product';

    protected $dataHelper;

    protected $historyFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Omnyfy\Approval\Helper\Data $dataHelper,
        \Omnyfy\Approval\Model\HistoryFactory $historyFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->historyFactory = $historyFactory;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
    }

    public function execute()
    {
        $recordId = $this->getRequest()->getParam('id');
        $productId = $this->getRequest()->getParam('product');
        $type = $this->getRequest()->getParam('type');

        if (empty($type) || ('approve' !== $type &&  'decline' !== $type && 'publish' !== $type) ) {
            $this->messageManager->addErrorMessage('Invalid action');
            $this->_redirect('omnyfy_approval/product/*');
            return;
        }

        if (empty($recordId) && empty($productId)) {
            $this->messageManager->addErrorMessage('Invalid record specified');
            $this->_redirect('omnyfy_approval/product/*');
            return;
        }

        $record = null;
        $toProductPage = false;

        if (empty($productId)) {
            //from approval grid，load by record_id
            $record = $this->dataHelper->getRecordById($recordId);
        }

        if (empty($recordId)) {
            //from product edit page, load by product_id
            $record = $this->dataHelper->getRecordByProductId($productId);
            $toProductPage = true;
        }

        if (empty($record)) {
            $this->messageManager->addErrorMessage('Invalid record specified');
            if ($toProductPage) {
                $this->_redirect('catalog/product/edit', ['id' => $productId]);
            }
            else{
                $this->_redirect('omnyfy_approval/product/*');
            }
            return;
        }

        $history = $this->historyFactory->create();
        $history->setParentId($record->getId());
        $history->setProductId($record->getProductId());
        $history->setBeforeStatus($record->getStatus());
        if ('approve' == $type) {
            $history->setAfterStatus(\Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_PASSED);
        }
        else if ('decline' == $type) {
            $history->setAfterStatus(\Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_FAILED);
        } else {
            $history->setAfterStatus(\Omnyfy\Approval\Model\Source\Status::STATUS_IN_PUBLISHING);
        }
        $history->setData('history_id', null);
        $history->setData('product_name', $record->getProductName());
        $history->setData('vendor_name', $record->getVendorName());

        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $history->addData($data);
        }

        $this->_coreRegistry->register('current_omnyfy_approval_product_history', $history);
        $this->_coreRegistry->register('current_omnyfy_approval_type', $type);
        if (!empty($productId)) {
            $this->_coreRegistry->register('current_omnyfy_approval_product_id', $productId);
        }

        $resultPage = $this->resultPageFactory->create();

        if ('approve' == $type) {
            $resultPage->getConfig()->getTitle()->prepend('Approve');
        } else if ('decline' == $type) {
            $resultPage->getConfig()->getTitle()->prepend('Decline');
        } else {
            $resultPage->getConfig()->getTitle()->prepend('Publish');
        }

        $resultPage->getLayout()->getBlock('omnyfy_approval_record_edit');

        return $resultPage;
    }
}
 