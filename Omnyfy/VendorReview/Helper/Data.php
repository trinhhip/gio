<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Omnyfy\VendorReview\Helper;

use Omnyfy\Vendor\Api\Data\VendorInterface;

/**
 * Default review helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_REVIEW_GUETS_ALLOW = 'catalog/review/allow_guest';
    const XML_ALLOW_SUBMIT_VENDOR_REVIEW_ON_ORDER = 'vendorreview/general/restrict_vendor_review';
    const XML_ALLOW_SUBMIT_PRODUCT_REVIEW_ON_ORDER = 'vendorreview/general/restrict_product_review';
    const XML_ASSIGN_VENDOR_TYPE = 'vendorreview/general/vendor_types';
    const XML_DEFAULT_OVERALL_RATING = 'vendorreview/general/rating_overall';

    protected $_scopeConfig;

    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Omnyfy\Vendor\Helper\Media
     */
    protected $_vendorMedia;

    /**
     * @var \Omnyfy\Vendor\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Magento session
     *
     * @var Magento\Customer\Model\Session
     */
    protected $_customerSession;

     /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    protected $_productFactory;

    protected $_helperImage;

    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Omnyfy\Vendor\Model\VendorFactory $vendorFactory
     * @param \Omnyfy\Vendor\Helper\Media $vendorMedia
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Framework\App\Http\Context $httpContext,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $helperImage,
        \Omnyfy\Vendor\Helper\Media $vendorMedia
    ) {
        $this->_escaper = $escaper;
        $this->filter = $filter;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->vendorFactory = $vendorFactory;
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->_vendorMedia = $vendorMedia;
        $this->_productFactory = $productFactory;
        $this->_helperImage = $helperImage;
        parent::__construct($context);
    }

    /**
     * Get review detail
     *
     * @param string $origDetail
     * @return string
     */
    public function getDetail($origDetail)
    {
        return nl2br($this->filter->truncate($origDetail, ['length' => 50]));
    }

    /**
     * Return short detail info in HTML
     *
     * @param string $origDetail Full detail info
     * @return string
     */
    public function getDetailHtml($origDetail)
    {
        return nl2br($this->filter->truncate($this->_escaper->escapeHtml($origDetail), ['length' => 50]));
    }

    /**
     * Return an indicator of whether or not guest is allowed to write
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsGuestAllowToWrite()
    {
        return false;
    }

    /**
     * Get review statuses with their codes
     *
     * @return array
     */
    public function getReviewStatuses()
    {
        return [
            \Omnyfy\VendorReview\Model\Review::STATUS_APPROVED => __('Approved'),
            \Omnyfy\VendorReview\Model\Review::STATUS_PENDING => __('Pending'),
            \Omnyfy\VendorReview\Model\Review::STATUS_NOT_APPROVED => __('Not Approved')
        ];
    }

    /**
     * Get review statuses as option array
     *
     * @return array
     */
    public function getReviewStatusesOptionArray()
    {
        $result = [];
        foreach ($this->getReviewStatuses() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * get vendor's logo by id
     *
     * @param int $vendorId
     * @return string|null
     */
    public function getImageVendor(int $vendorId): ?string
    {
        try {
            $vendor = $this->getVendorById($vendorId);
            if($vendor) {
                if($vendor->getLogo()) {
                    return $this->_vendorMedia->getVendorLogoUrl($vendor);
                }
                
            }
        } catch(\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return "";
    }

    /**
     * @param $vendorId
     * @return
     */
    public function getVendorById($vendorId): ?VendorInterface
    {
        if (empty($vendorId)) {
            return null;
        }
        return $this->vendorFactory->create()->load($vendorId);
    }

    public function getProductById($productId) 
    {
        if (!$productId) {
            return false;
        }

        return $this->_productFactory->create()->load($productId);
    }

    public function getImageProduct($productId) {
        $product = $this->getProductById($productId);

        return $this->_helperImage->init($product, 'product_thumbnail_image')->setImageFile($product->getFile())->getUrl();

    }

    public function getStore(){
        return $this->_storeManager->getStore();
    }

    public function isDisplayVendorReviewOnOrder() {
        return $this->_scopeConfig->isSetFlag(self::XML_ALLOW_SUBMIT_VENDOR_REVIEW_ON_ORDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    public function isDisplayProductReviewOnOrder() {
        return $this->_scopeConfig->isSetFlag(self::XML_ALLOW_SUBMIT_PRODUCT_REVIEW_ON_ORDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    public function getButtonReviewTitle() {
        return $this->_scopeConfig->getValue('vendorreview/general/feedback_title_button', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    public function getTitleHeaderTab() {
        return $this->_scopeConfig->getValue('vendorreview/general/title_feedback_header_tab', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }
    public function getAssignVendorType(){
        return $this->_scopeConfig->getValue(
            self::XML_ASSIGN_VENDOR_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getDefaultOverallRating(){
        return $this->_scopeConfig->getValue(
            self::XML_DEFAULT_OVERALL_RATING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
