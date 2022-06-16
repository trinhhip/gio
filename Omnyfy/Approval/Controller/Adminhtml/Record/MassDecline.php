<?php

namespace Omnyfy\Approval\Controller\Adminhtml\Record;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Omnyfy\Approval\Model\Resource\Product\CollectionFactory;

class MassDecline extends Action
{
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Omnyfy\Core\Helper\Email
     */
    private $emailHelper;
    /**
     * @var \Omnyfy\Approval\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Omnyfy\Vendor\Api\VendorRepositoryInterface
     */
    private $vendorRepository;
    /**
     * @var \Omnyfy\Vendor\Model\Resource\Profile
     */
    private $profileResource;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Omnyfy\Approval\Model\HistoryFactory
     */
    private $historyFactory;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Omnyfy\Core\Helper\Email $emailHelper,
        \Omnyfy\Approval\Helper\Data $dataHelper,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Vendor\Model\Resource\Profile $profileResource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Omnyfy\Approval\Model\HistoryFactory $historyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->emailHelper = $emailHelper;
        $this->dataHelper = $dataHelper;
        $this->vendorRepository = $vendorRepository;
        $this->profileResource = $profileResource;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->historyFactory = $historyFactory;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        if ($collectionSize) {
            try {
                foreach ($collection->getItems() as $item) {
                    $historyModel = $this->historyFactory->create();
                    $historyModel->setData('parent_id',$item->getId());
                    $historyModel->setData('product_id',$item->getProductId());
                    $historyModel->setData('before_status',$item->getStatus());
                    $historyModel->setData('after_status',\Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_FAILED);
                    $historyModel->setData('created_at', $item->getCreatedAt());
                    $item->setData('status', \Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_FAILED);
                    $product = $this->productRepository->getById($item->getProductId());
                    $product->setData('approval_status', \Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_FAILED);
                    $product->getResource()->saveAttribute($product, 'status')
                        ->saveAttribute($product, 'approval_status');
                    $item->save();
                    $historyModel->save();
                    $record = $this->dataHelper->getRecordById($item->getId());
                    $vendor = $this->vendorRepository->getById($record->getVendorId());
                    $websiteIds = array_keys($this->profileResource->getProfileIdsByVendorId($vendor->getId()));
                    $storeId = $this->dataHelper->getStoreId($websiteIds[0]);
                    $data = [
                        'vendor_name' => $vendor->getName(),
                        'product_name' => $record->getProductName(),
                        'sku' => $record->getSku(),
                        'website' => $this->dataHelper->getWebsiteNames($websiteIds),
                        'link_url' => $this->getUrl('catalog/product/view', ['_scope' => $storeId, 'id' => $record->getProductId()]),
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
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were review failed', $collectionSize)
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $this->_redirect('*/product/index');
    }
}
