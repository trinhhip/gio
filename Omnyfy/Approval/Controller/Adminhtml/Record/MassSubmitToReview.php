<?php

namespace Omnyfy\Approval\Controller\Adminhtml\Record;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class MassSubmitToReview extends Action
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

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Omnyfy\Approval\Helper\Data $dataHelper,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Vendor\Model\Resource\Profile $profileResource,
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
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create()->addAttributeToSelect('*'));
        $collectionSize = $collection->getSize();
        $vendorInfo = $this->getBackendSession()->getVendorInfo();
        if (empty($vendorInfo)) {
            $this->messageManager->addErrorMessage(__('Only Vendor can submit to review'));
            return $this->_redirect('catalog/product/index');
        }
        if ($collectionSize) {
            try {
                foreach ($collection->getItems() as $item) {
                    $this->dataHelper->saveProductRecord(
                        $item->getId(),
                        $item->getSku(),
                        $vendorInfo['vendor_id'],
                        \Omnyfy\Approval\Model\Source\Status::STATUS_SUBMITTED_TO_REVIEW,
                        $item->getName(),
                        $vendorInfo['vendor_name']
                    );
                    $item->setData('approval_status', \Omnyfy\Approval\Model\Source\Status::STATUS_SUBMITTED_TO_REVIEW);
                    $item->getResource()->saveAttribute($item, 'approval_status');
                    $item->save();
                    $vendor = $this->vendorRepository->getById($vendorInfo['vendor_id']);
                    $websiteIds = array_keys($this->profileResource->getProfileIdsByVendorId($vendor->getId()));
                    $storeId = $this->dataHelper->getStoreId($websiteIds[0]);
                    $data = [
                        'vendor_name' => $vendorInfo['vendor_name'],
                        'product_name' => $item->getName(),
                        'sku' => $item->getSku(),
                        'website' => $this->dataHelper->getWebsiteNames($websiteIds),
                        'link_url' => $this->getUrl('catalog/product/view', ['_scope' => $storeId, 'id' => $item->getId()]),
                    ];
                    $obj = new \Magento\Framework\DataObject();
                    $obj->addData($data);
                    $vars = ['data' => $obj];
                    $this->emailHelper->sendEmail(
                        $this->dataHelper->getEmailTemplateSubmittedToReview(),
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
                    __('Total of %1 record(s) were submitted to review', $collectionSize)
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
