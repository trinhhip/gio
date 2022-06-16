<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-21
 * Time: 11:00
 */

namespace Omnyfy\Approval\Controller\Adminhtml\Record;

use Omnyfy\Vendor\Controller\Adminhtml\AbstractAction;

class Save extends AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_Approval::product';

    protected $historyFactory;

    protected $recordResource;

    protected $productRepository;

    protected $emailHelper;

    protected $dataHelper;

    protected $vendorRepository;

    protected $profileResource;

    protected $storeManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Omnyfy\Approval\Model\HistoryFactory $historyFactory,
        \Omnyfy\Approval\Model\Resource\Product $recordResource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Omnyfy\Core\Helper\Email $emailHelper,
        \Omnyfy\Approval\Helper\Data $dataHelper,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Vendor\Model\Resource\Profile $profileResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->historyFactory = $historyFactory;
        $this->recordResource = $recordResource;
        $this->productRepository = $productRepository;
        $this->emailHelper = $emailHelper;
        $this->dataHelper = $dataHelper;
        $this->vendorRepository = $vendorRepository;
        $this->profileResource = $profileResource;
        $this->storeManager = $storeManager;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
    }

    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        $productId = $this->getRequest()->getParam('product', null);
        $data = $this->getRequest()->getPostValue();

        $backUrl = $this->getUrl('omnyfy_approval/product/index');
        $params = [];
        if (!empty($productId)) {
            $backUrl = $this->getUrl('catalog/product/edit', ['id' => $productId]);
            $params['product'] = $productId;
        } else {
            $params['id'] = $data['parent_id'];
        }
        $returnUrl = $this->getUrl('omnyfy_approval/record/' . $type, $params);

        $history = $this->historyFactory->create();
        $history->addData($data);
        $history->setId(null);

        try {
            $history->save();
            //update record status
            $this->recordResource->updateById('status', $history->getAfterStatus(), $history->getParentId());

            $msg = '';
            $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
            $approvalStatus = \Omnyfy\Approval\Model\Source\Status::STATUS_SUBMITTED_TO_REVIEW;
            switch ($type) {
                case 'approve':
                    $msg = 'Approved product successfully';
                    $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
                    $approvalStatus = \Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_PASSED;
                    $this->sendSuccessEmail($history->getParentId());
                    break;
                case 'decline':
                    $msg = 'Declined product successfully';
                    $approvalStatus = \Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_FAILED;
                    $this->sendFailedEmail($history->getParentId());
                    break;
                case 'publish':
                    $msg = 'Published product successfully';
                    $approvalStatus = \Omnyfy\Approval\Model\Source\Status::STATUS_IN_PUBLISHING;
                    break;
            }
            $this->updateProductStatus($history->getProductId(), $status, $approvalStatus);

            $this->messageManager->addSuccessMessage($msg);
            $this->_redirect($backUrl);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Something wrong when saving comments');
            $this->_redirect($returnUrl);
        }

        $this->_redirect($backUrl);
        return;
    }

    protected function updateProductStatus($productId, $status, $approvalStatus)
    {
        $product = $this->productRepository->getById($productId);
        $product->setStatus($status);
        $product->setData('approval_status', $approvalStatus);
        $product->getResource()->saveAttribute($product, 'status')
            ->saveAttribute($product, 'approval_status');
    }

    protected function sendSuccessEmail($recordId)
    {
        $record = $this->dataHelper->getRecordById($recordId);
        $vendor = $this->vendorRepository->getById($record->getVendorId());
        $websiteIds = array_keys($this->profileResource->getProfileIdsByVendorId($vendor->getId()));
        $storeId = $this->dataHelper->getStoreId($websiteIds[0]);

        $data = [
            'vendor_name' => $vendor->getName(),
            'product_name' => $record->getProductName(),
            'sku' => $record->getSku(),
            'website' => $this->dataHelper->getWebsiteNames($websiteIds),
            'link_url' => $this->getUrl('catalog/product/view', ['_scope' => $storeId, 'id' => $record->getProductId()]),
            'store_name' => $this->storeManager->getStore()->getName(),
            'comment' => $this->getRequest()->getParam('comment')
        ];
        $obj = new \Magento\Framework\DataObject();
        $obj->addData($data);
        $vars = ['data' => $obj];

        $this->emailHelper->sendEmail(
            $this->dataHelper->getEmailTemplateApproval(),
            $vars,
            $this->dataHelper->getSender(),
            [
                'name' => $vendor->getName(),
                'email' => $vendor->getEmail()
            ],
            \Magento\Framework\App\Area::AREA_FRONTEND,
            $this->storeManager->getStore()->getId(),
            [],
            $this->dataHelper->getEmailCopyTo()
        );
    }

    protected function sendFailedEmail($recordId)
    {
        $record = $this->dataHelper->getRecordById($recordId);
        $vendor = $this->vendorRepository->getById($record->getVendorId());
        $websiteIds = array_keys($this->profileResource->getProfileIdsByVendorId($vendor->getId()));

        $data = [
            'vendor_name' => $vendor->getName(),
            'product_name' => $record->getProductName(),
            'sku' => $record->getSku(),
            'website' => $this->dataHelper->getWebsiteNames($websiteIds),
            'comment' => $this->getRequest()->getParam('comment')
        ];
        $obj = new \Magento\Framework\DataObject();
        $obj->addData($data);
        $vars = ['data' => $obj];

        $this->emailHelper->sendEmail(
            $this->dataHelper->getEmailTemplateReviewFail(),
            $vars,
            $this->dataHelper->getSender(),
            [
                'name' => $vendor->getName(),
                'email' => $vendor->getEmail()
            ],
            \Magento\Framework\App\Area::AREA_FRONTEND,
            $this->storeManager->getStore()->getId(),
            [],
            $this->dataHelper->getEmailCopyTo()
        );
    }
}
