<?php


namespace OmnyfyCustomzation\Vendor\Block\Vendor\Listing;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Omnyfy\Vendor\Helper\Media;
use Omnyfy\Vendor\Model\VendorFactory;
use OmnyfyCustomzation\Vendor\Block\Vendor\Listing as VendorListing;
use OmnyfyCustomzation\Vendor\Helper\Data;

class Pager extends VendorListing
{
    public $pageSizes = [10, 20, 50];
    public $pageLength = 5;
    /**
     * @var UrlInterface
     */
    public $urlBuilder;

    public function __construct(
        Template\Context $context,
        VendorFactory $vendorFactory,
        Media $helper,
        Data $helperData,
        UrlInterface $urlBuilder,
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $vendorFactory, $helper, $helperData, $data);
    }

    public function getNumberPage()
    {
        $collectionSize = $this->getLoadedVendorCollection()->getSize();
        $pageSize = $this->getPageSize();
        return $collectionSize && $pageSize ? ceil($collectionSize / $pageSize) : 1;
    }

    public function getPageUrl($page)
    {
        $params = [self::PAGE_VAR_NAME => $page];
        $limit = $this->getRequest()->getParam(self::LIMIT_VAR_NAME);
        $pageSizes = $this->getNumberPageShow();
        if ($limit && $limit != $pageSizes[0]) {
            $params = array_merge($params, [self::LIMIT_VAR_NAME => $limit]);
        }
        return $this->getUrl('shop/brands', $params);
    }

    public function getNumberPageShow()
    {
        return $this->pageSizes;
    }

    public function isJumpPage($numberPage)
    {
        // Can JumpPage if have more 5 page
        if ($numberPage < 6) {
            return false;
        }
        // Jump with 4 page
        $currentPage = $this->getCurrentPage();
        if ((ceil($currentPage / 4)) > 0 && ($currentPage + 3) < $numberPage) {
            return true;
        }
        return false;
    }

    public function getPageStart($numberPage)
    {
        $currentPage = $this->getCurrentPage();
        $pageStart = 1;
        for ($i = (int)($numberPage / $this->pageLength); $i > 0; $i--) {
            $page = ($this->pageLength * $i) - ($i - 1);
            if ($page <= $currentPage) {
                $pageStart = $page;
                break;
            }
        }
        return $pageStart;
    }

    public function getPageLength()
    {
        return $this->pageLength;
    }

}
