<?php

namespace Omnyfy\Approval\Controller\Adminhtml\Record;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Omnyfy\Approval\Model\Product;

class MassApproveProduct extends Action
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
     * @var \Omnyfy\Core\Helper\Email
     */
    private $emailHelper;
    /**
     * @var \Omnyfy\Approval\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Omnyfy\Approval\Model\HistoryFactory
     */
    private $historyFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Omnyfy\Approval\Helper\Data $dataHelper,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Vendor\Model\Resource\Profile $profileResource,
        \Omnyfy\Approval\Model\ProductFactory $productFactory,
        \Omnyfy\Approval\Model\HistoryFactory $historyFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Omnyfy\Core\Helper\Email $emailHelper
    )
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->vendorRepository = $vendorRepository;
        $this->profileResource = $profileResource;
        $this->storeManager = $storeManager;
        $this->emailHelper = $emailHelper;
        $this->productFactory = $productFactory;
        $this->historyFactory = $historyFactory;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create()->addAttributeToSelect('*'));
        $collectionSize = $collection->getSize();
        $vendorInfo = $this->getBackendSession()->getVendorInfo();
        if ($collectionSize) {
            try {
                foreach ($collection->getItems() as $item) {
                    $approvalProductModel = $this->productFactory->create();
                    if (!empty($approvalProduct = $approvalProductModel->getCollection()->addFieldToFilter('product_id', $item->getId())->getItems())) {
                        foreach ($approvalProduct as $record) {
                            $historyModel = $this->historyFactory->create();
                            $historyModel->setData('parent_id', $record->getId());
                            $historyModel->setData('product_id', $item->getId());
                            $historyModel->setData('before_status', $record->getStatus());
                            $historyModel->setData('after_status', \Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_PASSED);
                            $historyModel->setData('created_at', $record->getCreatedAt());
                            $approvalProductModel->load($record->getId());
                            $approvalProductModel->setData('status', \Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_PASSED);
                            $item->setData('approval_status', \Omnyfy\Approval\Model\Source\Status::STATUS_REVIEW_PASSED);
                            $item->save();
                            $historyModel->save();
                            $approvalProductModel->save();
                            $vendor = $this->vendorRepository->getById($record->getVendorId());
                            $websiteIds = array_keys($this->profileResource->getProfileIdsByVendorId($vendor->getId()));
                            $storeId = $this->dataHelper->getStoreId($websiteIds[0]);
                            $data = [
                                'vendor_name' => $vendor->getName(),
                                'product_name' => $record->getProductName(),
                                'sku' => $record->getSku(),
                                'website' => $this->dataHelper->getWebsiteNames($websiteIds),
                                'link_url' => $this->getUrl('catalog/product/view', ['_scope' => $storeId, 'id' => $record->getProductId()]),
                                'store_name' => $this->storeManager->getStore()->getName()
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
                    }
                }
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 record(s) were review passed', $collectionSize)
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $this->_redirect('catalog/product/index');
    }

    protected function getBackendSession()
    {
        if (null == $this->_session) {
            $this->_session = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Backend\Model\Session::class);
        }
        return $this->_session;
    }
}
