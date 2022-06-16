<?php
namespace Omnyfy\Vendor\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;

class Save extends \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save
{
    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var Session
     */
    protected $_session;

    protected $itemFactory;

    protected $vSourceStock;

    protected $inventoryResource;

    protected $dataHelper;

    public function __construct(
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        Action\Context $context,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        CreditmemoSender $creditmemoSender,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStock,
        \Omnyfy\vendor\Model\Resource\Inventory $inventoryResource,
        \Omnyfy\Vendor\Helper\Data $dataHelper
    ) {
        $this->itemFactory = $itemFactory;
        $this->vSourceStock = $vSourceStock;
        $this->inventoryResource = $inventoryResource;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $creditmemoLoader, $creditmemoSender, $resultForwardFactory);
    }

    public function execute() {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPost('creditmemo');
        $vendorId = 0;
        $countQtyIsZero = 0;
        $arrayVendor = [];
        $arrReturnToInventory = [];
        foreach ($data['items'] as $key => $value) {
            if($value['qty'] > 0) {
                $order_item = $this->itemFactory->create()->load($key);
                $websiteCode = $order_item->getOrder()->getStore()->getWebsite()->getCode();
                $stockId = $this->dataHelper->getStockIdByWebsiteCode($websiteCode);
                $vendor_id = $order_item->getData('vendor_id');
                $arrayVendor[$vendor_id] = $vendor_id;
                $vendorId = $vendor_id;
                $sourceStockId = $order_item->getSourceStockId();
                $sourceCode = $this->vSourceStock->getSourceCodeById($sourceStockId);

                $arrReturnToInventory[] = [
                    'source_code' => $sourceCode,
                    'product_id' => $order_item->getProductId(),
                    'qty' => $value['qty'],
                    'back_to_stock' => isset($value['back_to_stock']) ? true : false,
                    'stock_id' => $stockId,
                    'sku' => $order_item->getSku()
                ];
            } else {
                $countQtyIsZero++;
            }
            if(count($arrayVendor) > 1) {
                $this->messageManager->addErrorMessage(__('MO can’t create Credit memos with multiple vendor'));
                $this->_session->setFormData($data);
                $resultRedirect->setPath('sales/*/new', ['_current' => true]);
                return $resultRedirect;
            }
        }
        if ($countQtyIsZero == count($data['items'])) {
            $this->messageManager->addErrorMessage(__("Can't create Credite momos with all Qty to Refunds are 0."));
            $this->_session->setFormData($data);
            $resultRedirect->setPath('sales/*/new', ['_current' => true]);
            return $resultRedirect;
        }
        if (!empty($data['comment_text'])) {
            $this->_getSession()->setCommentText($data['comment_text']);
        }
        try {
            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $this->creditmemoLoader->load();
            if ($creditmemo) {
                $creditmemo->setVendorId($vendorId);

                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }

                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $creditmemo->setCustomerNote($data['comment_text']);
                    $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }

                if (isset($data['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                }
                $creditmemoManagement = $this->_objectManager->create(
                    \Magento\Sales\Api\CreditmemoManagementInterface::class
                );
                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $doOffline = isset($data['do_offline']) ? (bool)$data['do_offline'] : false;
                $creditmemoManagement->refund($creditmemo, $doOffline);

                if (count($arrReturnToInventory)) {
                    foreach ($arrReturnToInventory as $dataReturn) {
                        if ($dataReturn['back_to_stock']) {
                            $this->inventoryResource->returnQty($dataReturn);
                        }
                    }
                }

                if (!empty($data['send_email'])) {
                    $this->creditmemoSender->send($creditmemo);
                }

                $this->messageManager->addSuccessMessage(__('You created the credit memo.'));
                $this->_getSession()->getCommentText(true);
                $resultRedirect->setPath('sales/order/view', ['order_id' => $creditmemo->getOrderId()]);
                return $resultRedirect;
            } else {
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_getSession()->setFormData($data);
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addErrorMessage(__('We can\'t save the credit memo right now.'));
        }
        $resultRedirect->setPath('sales/*/new', ['_current' => true]);
        return $resultRedirect;
    }

    public function getStockId(string $websiteCode)
    {
        if (empty($websiteCode)) {
            return;
        }

        return $this->dataHelper->getStockIdByWebsiteCode($websiteCode);
    }
}
