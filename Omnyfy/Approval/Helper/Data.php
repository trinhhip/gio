<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 12:15
 */

namespace Omnyfy\Approval\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'omnyfy_approval/general/enabled';

    const XML_PATH_EMAIL_TEMPLATE_REVIEW_FAIL = 'omnyfy_approval/general/email_template_fail';

    const XML_PATH_EMAIL_TEMPLATE_SUBMITTED_TO_REVIEW = 'omnyfy_approval/general/email_template_submit';

    const XML_PATH_EMAIL_TEMPLATE_REVIEW_PASS = 'omnyfy_approval/general/email_template_pass';

    const XML_PATH_EMAIL_TEMPLATE_APPROVAL = 'omnyfy_approval/general/email_template_approval';

    const XML_PATH_EMAIL_COPY_TO = 'omnyfy_approval/general/copy_to';

    const XML_PATH_SENDER = 'omnyfy_approval/general/sender';

    protected $productCollectionFactory;

    protected $logger;

    protected $productResource;

    protected $vendorConfig;

    protected $moVendorIds;

    protected $storeManager;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    public function __construct(
        Context $context,
        \Omnyfy\Approval\Model\Resource\Product\CollectionFactory $productCollectionFactory,
        \Omnyfy\Approval\Model\Resource\Product $productResource,
        \Omnyfy\Vendor\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productResource = $productResource;
        $this->vendorConfig = $config;
        $this->storeManager = $storeManager;
        parent::__construct($context);
        $this->logger = $context->getLogger();
        $this->moVendorIds = $this->vendorConfig->getMOVendorIds();
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    public function getEmailTemplateReviewPass()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE_REVIEW_PASS);
    }

    public function getEmailTemplateReviewFail()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE_REVIEW_FAIL);
    }

    public function getEmailTemplateSubmittedToReview() {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE_SUBMITTED_TO_REVIEW);
    }

    public function getEmailTemplateApproval() {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE_APPROVAL);
    }

    public function getEmailCopyTo()
    {
        $emailCopyTo = $this->getConfigValue(self::XML_PATH_EMAIL_COPY_TO);
        if (!empty($emailCopyTo)) {
            $emailCopyTo = explode(',', $emailCopyTo);
        } else {
            $emailCopyTo = [];
        }
        return $emailCopyTo;
    }

    public function getSender()
    {
        return $this->getConfigValue(self::XML_PATH_SENDER);
    }

    public function getProductRecord($productId, $vendorId)
    {
        if (empty($productId) || empty($vendorId)) {
            return false;
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('vendor_id', $vendorId)
            ->setPageSize(1);

        $record = $collection->getFirstItem();
        if (empty($record->getId())) {
            return false;
        }

        return $record;
    }

    public function saveProductRecord($productId, $sku, $vendorId, $status, $productName, $vendorName)
    {
        $data = [
            'product_id' => $productId,
            'sku' => $sku,
            'product_name' => $productName,
            'vendor_id' => $vendorId,
            'vendor_name' => $vendorName,
            'status' => $status
        ];
        $this->productResource->bulkSave([$data]);
    }

    public function getRecordById($id)
    {
        if (empty($id)) {
            return false;
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('id', $id)
            ->setPageSize(1);

        $record = $collection->getFirstItem();
        if (empty($record->getId())) {
            return false;
        }

        return $record;
    }

    public function getRecordByProductId($productId)
    {
        if (empty($productId)) {
            return false;
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('product_id', $productId)
            ->setPageSize(1);

        $record = $collection->getFirstItem();
        if (empty($record->getId())) {
            return false;
        }

        return $record;
    }

    public function isMoVendor($vendorId)
    {
        return in_array($vendorId, $this->moVendorIds);
    }

    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    public function getWebsiteNames($ids)
    {
        $arr = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            if (in_array($website->getId(), $ids)) {
                $arr[] = $website->getName();
            }
        }
        return implode(',', $arr);
    }

    public function getStoreId($websiteId)
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            if ($websiteId == $store->getWebsiteId()) {
                return $store->getId();
            }
        }
        return 0;
    }
}
 