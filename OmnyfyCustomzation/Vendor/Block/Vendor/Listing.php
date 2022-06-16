<?php


namespace OmnyfyCustomzation\Vendor\Block\Vendor;


use Magento\Framework\View\Element\Template;
use Omnyfy\Vendor\Helper\Media;
use Omnyfy\Vendor\Model\VendorFactory;
use OmnyfyCustomzation\Vendor\Helper\Data;

class Listing extends \Omnyfy\Vendor\Block\Vendor\Listing
{
    const PAGE_VAR_NAME = 'p';
    const LIMIT_VAR_NAME = 'limit';
    const IS_HIDE_VENDOR = 1;
    /**
     * @var Data
     */
    public $helperData;

    public function __construct(
        Template\Context $context,
        VendorFactory $vendorFactory,
        Media $helper,
        Data $helperData,
        array $data = []
    )
    {
        $this->helperData = $helperData;
        parent::__construct($context, $vendorFactory, $helper, $data);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Our Creators'));
        parent::_prepareLayout();
        return $this;
    }

    public function getProductImage($product)
    {
        return $this->helperData->getProductImage($product);
    }

    public function getProductByVendor($vendor)
    {
        return $this->helperData->getProductByVendor($vendor);
    }

    public function getVendorSidebar()
    {
        return $this->getVendorCollection();
    }

    public function getFirstCharNameVendor($vendorName)
    {
        return strtoupper(substr($vendorName, 0, 1));
    }

    public function getVendorCollection($pageSize = null, $page = null)
    {
        $collection = [];
        $vendors = $this->getLoadedVendorCollection();
        $vendors->setOrder('name', 'ASC');
        if ($pageSize) {
            $vendors->setPageSize($pageSize);
        }
        if ($page) {
            $vendors->setCurPage($page);
        }
        foreach ($vendors as $key => $vendor) {
            if ($vendor->getHideVendor() != self::IS_HIDE_VENDOR ){
                $alphabet = $this->getFirstCharNameVendor($vendor->getName());
                $collection[$alphabet][$key] = $vendor;
            }
        }
        return $collection;
    }

    public function getVendorList()
    {
        $pageSize = $this->getPageSize();
        $currentPage = $this->getCurrentPage();
        return $this->getVendorCollection($pageSize, $currentPage);
    }

    public function getPageSize()
    {
        $limit = $this->getRequest()->getParam(self::LIMIT_VAR_NAME);
        return $limit ? $limit : 10;
    }

    public function getCurrentPage()
    {
        $page = $this->getRequest()->getParam(self::PAGE_VAR_NAME);
        return $page ? $page : 1;
    }

    public function getBanner()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $banner = $this->helperData->getBannerImage();
        return $mediaUrl . 'porto/sticky_logo/' . $banner;
    }

}
